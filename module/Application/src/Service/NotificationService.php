<?php

declare(strict_types=1);

namespace Application\Service;

use Application\Entity\Appointment;
use Application\Entity\Notification;
use DateTime;
use Doctrine\ORM\EntityManager;
use Laminas\Mail\Message;
use Laminas\Mail\Transport\TransportInterface;
use Throwable;

class NotificationService
{
    private EntityManager $entityManager;
    private TransportInterface $mailTransport;
    private string $smsApiKey;
    private string $smsApiUrl;
    private string $emailFrom;

    public function __construct(
        EntityManager $entityManager,
        TransportInterface $mailTransport,
        string $emailFrom,
        string $smsApiKey,
        string $smsApiUrl
    ) {
        $this->entityManager = $entityManager;
        $this->mailTransport = $mailTransport;
        $this->emailFrom = $emailFrom;
        $this->smsApiKey = $smsApiKey;
        $this->smsApiUrl = $smsApiUrl;
    }

    public function sendAppointmentNotification(Appointment $appointment, string $message): void
    {
        $user = $appointment->getUser();
        
        // Cria notificação por e-mail
        if ($user->getEmail()) {
            $this->createNotification(
                $appointment,
                Notification::TYPE_EMAIL,
                $this->formatEmailMessage($appointment, $message)
            );
        }

        // Cria notificação por SMS se o usuário tiver telefone cadastrado
        if ($user->getPhone()) {
            $this->createNotification(
                $appointment,
                Notification::TYPE_SMS,
                $this->formatSmsMessage($appointment, $message)
            );
        }

        // Processa notificações pendentes
        $this->processNotifications();
    }

    public function processNotifications(): void
    {
        $notifications = $this->entityManager
            ->getRepository(Notification::class)
            ->findBy(['status' => Notification::STATUS_PENDING]);

        foreach ($notifications as $notification) {
            try {
                if ($notification->getType() === Notification::TYPE_EMAIL) {
                    $this->sendEmail($notification);
                } else {
                    $this->sendSms($notification);
                }

                $notification->setStatus(Notification::STATUS_SENT);
            } catch (Throwable $e) {
                $notification->setStatus(Notification::STATUS_FAILED);
            }
        }

        $this->entityManager->flush();
    }

    private function createNotification(
        Appointment $appointment,
        string $type,
        string $message
    ): Notification {
        $notification = new Notification();
        $notification->setUser($appointment->getUser())
            ->setAppointment($appointment)
            ->setType($type)
            ->setMessage($message);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        return $notification;
    }

    private function sendEmail(Notification $notification): void
    {
        $user = $notification->getUser();
        $appointment = $notification->getAppointment();

        $message = new Message();
        $message->setFrom($this->emailFrom)
            ->setTo($user->getEmail())
            ->setSubject('Notificação de Agendamento - Cartório')
            ->setBody($notification->getMessage());

        $this->mailTransport->send($message);
    }

    private function sendSms(Notification $notification): void
    {
        $user = $notification->getUser();
        
        // Aqui você implementaria a integração com o serviço de SMS
        // Este é apenas um exemplo usando cURL
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->smsApiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'to' => $user->getPhone(),
                'message' => $notification->getMessage(),
                'api_key' => $this->smsApiKey
            ]),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ]
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            throw new \RuntimeException('Erro ao enviar SMS: ' . $error);
        }
    }

    private function formatEmailMessage(Appointment $appointment, string $message): string
    {
        $service = $appointment->getService();
        $date = $appointment->getAppointmentDate()->format('d/m/Y H:i');

        return <<<MESSAGE
        Olá {$appointment->getUser()->getName()},

        {$message}

        Detalhes do agendamento:
        Serviço: {$service->getName()}
        Data: {$date}
        Status: {$appointment->getStatus()}

        Caso tenha alguma dúvida, entre em contato conosco.

        Atenciosamente,
        Equipe do Cartório
        MESSAGE;
    }

    private function formatSmsMessage(Appointment $appointment, string $message): string
    {
        $date = $appointment->getAppointmentDate()->format('d/m/Y H:i');
        return "{$message} Agendamento: {$appointment->getService()->getName()} - {$date}";
    }

    public function retryFailedNotifications(): void
    {
        $failedNotifications = $this->entityManager
            ->getRepository(Notification::class)
            ->findBy(['status' => Notification::STATUS_FAILED]);

        foreach ($failedNotifications as $notification) {
            $notification->setStatus(Notification::STATUS_PENDING);
        }

        $this->entityManager->flush();
        $this->processNotifications();
    }

    public function getNotificationHistory(Appointment $appointment): array
    {
        return $this->entityManager
            ->getRepository(Notification::class)
            ->findBy(
                ['appointment' => $appointment],
                ['createdAt' => 'DESC']
            );
    }
} 
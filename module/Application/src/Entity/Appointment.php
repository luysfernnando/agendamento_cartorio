<?php

declare(strict_types=1);

namespace Application\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Laminas\Hydrator\ClassMethodsHydrator;

/**
 * @ORM\Entity(repositoryClass="Application\Repository\AppointmentRepository")
 * @ORM\Table(name="appointments")
 */
class Appointment
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_COMPLETED = 'completed';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    private User $user;

    /**
     * @ORM\ManyToOne(targetEntity="Service")
     * @ORM\JoinColumn(name="service_id", referencedColumnName="id", nullable=false)
     */
    private Service $service;

    /**
     * @ORM\Column(name="appointment_date", type="datetime")
     */
    private DateTimeInterface $appointmentDate;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $notes = null;

    /**
     * @ORM\Column(type="string", length=20, columnDefinition="ENUM('pending', 'confirmed', 'cancelled', 'completed')")
     */
    private string $status = self::STATUS_PENDING;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    private DateTimeInterface $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private DateTimeInterface $updatedAt;

    /**
     * @ORM\Column(name="cancelled_at", type="datetime", nullable=true)
     */
    private ?DateTimeInterface $cancelledAt = null;

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getService(): Service
    {
        return $this->service;
    }

    public function setService(Service $service): self
    {
        $this->service = $service;
        return $this;
    }

    public function getAppointmentDate(): DateTimeInterface
    {
        return $this->appointmentDate;
    }

    public function setAppointmentDate(DateTimeInterface $appointmentDate): self
    {
        $this->appointmentDate = $appointmentDate;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        if (!in_array($status, [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
            self::STATUS_CANCELLED,
            self::STATUS_COMPLETED
        ])) {
            throw new \InvalidArgumentException('Status inválido');
        }

        $this->status = $status;
        return $this;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function getCancelledAt(): ?DateTimeInterface
    {
        return $this->cancelledAt;
    }

    public function setCancelledAt(?DateTimeInterface $cancelledAt): self
    {
        $this->cancelledAt = $cancelledAt;
        return $this;
    }

    public function cancel(): self
    {
        $this->status = self::STATUS_CANCELLED;
        $this->cancelledAt = new DateTime();
        return $this;
    }

    public function canBeCancelled(): bool
    {
        if ($this->status === self::STATUS_CANCELLED) {
            return false;
        }

        $now = new DateTime();
        $diff = $this->appointmentDate->diff($now);

        // Permite cancelamento até 24 horas antes do agendamento
        return $diff->invert === 1 && ($diff->days > 0 || $diff->h >= 24);
    }

    public function toArray(): array
    {
        $hydrator = new ClassMethodsHydrator();
        return $hydrator->extract($this);
    }
} 
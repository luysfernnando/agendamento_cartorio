$(document).ready(function() {
    // Inicializa todos os tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Inicializa todos os popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Adiciona a classe fade-in aos elementos que devem ter animação
    $('.fade-in').each(function() {
        $(this).css('opacity', '0');
        $(this).waypoint(function() {
            $(this.element).addClass('animated');
        }, {
            offset: '90%'
        });
    });

    // Fecha alertas automaticamente após 5 segundos
    $('.alert:not(.alert-permanent)').delay(5000).fadeOut(500);

    // Confirma ações destrutivas
    $('[data-confirm]').on('click', function(e) {
        if (!confirm($(this).data('confirm'))) {
            e.preventDefault();
        }
    });

    // Desabilita múltiplos submits em formulários
    $('form').on('submit', function() {
        var $form = $(this);
        var $submitButton = $form.find('button[type="submit"]');
        
        if ($form.data('submitted') === true) {
            e.preventDefault();
        } else {
            $form.data('submitted', true);
            $submitButton.prop('disabled', true);
            
            // Adiciona spinner ao botão
            var originalText = $submitButton.html();
            $submitButton.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Aguarde...');
            
            // Restaura o botão após 30 segundos (timeout de segurança)
            setTimeout(function() {
                $form.data('submitted', false);
                $submitButton.prop('disabled', false).html(originalText);
            }, 30000);
        }
    });

    // Formata campos de telefone
    $('input[type="tel"]').on('input', function() {
        var phone = $(this).val().replace(/\D/g, '');
        var formattedPhone = '';
        
        if (phone.length > 0) {
            formattedPhone = '(' + phone.substring(0,2);
            if (phone.length > 2) {
                formattedPhone += ') ' + phone.substring(2,7);
                if (phone.length > 7) {
                    formattedPhone += '-' + phone.substring(7,11);
                }
            }
        }
        
        $(this).val(formattedPhone);
    });

    // Formata campos de CPF
    $('input[data-mask="cpf"]').on('input', function() {
        var cpf = $(this).val().replace(/\D/g, '');
        var formattedCpf = '';
        
        if (cpf.length > 0) {
            formattedCpf = cpf.substring(0,3);
            if (cpf.length > 3) {
                formattedCpf += '.' + cpf.substring(3,6);
                if (cpf.length > 6) {
                    formattedCpf += '.' + cpf.substring(6,9);
                    if (cpf.length > 9) {
                        formattedCpf += '-' + cpf.substring(9,11);
                    }
                }
            }
        }
        
        $(this).val(formattedCpf);
    });

    // Formata campos de moeda
    $('input[data-mask="currency"]').on('input', function() {
        var value = $(this).val().replace(/\D/g, '');
        value = (value/100).toFixed(2);
        value = value.replace('.', ',');
        value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
        $(this).val('R$ ' + value);
    });

    // Atualiza horários disponíveis quando a data ou serviço mudam
    $('#appointment_date, #service_id').on('change', function() {
        var $serviceSelect = $('#service_id');
        var $dateInput = $('#appointment_date');
        var $timeSelect = $('#appointment_time');
        
        if ($serviceSelect.val() && $dateInput.val()) {
            $.get('/appointment/get-available-times', {
                service_id: $serviceSelect.val(),
                date: $dateInput.val()
            })
            .done(function(response) {
                $timeSelect.empty();
                $timeSelect.append($('<option>').val('').text('Selecione um horário'));
                
                response.times.forEach(function(time) {
                    $timeSelect.append($('<option>').val(time).text(time));
                });
                
                $timeSelect.prop('disabled', false);
            })
            .fail(function() {
                alert('Erro ao carregar horários disponíveis. Por favor, tente novamente.');
            });
        } else {
            $timeSelect.empty();
            $timeSelect.append($('<option>').val('').text('Selecione um horário'));
            $timeSelect.prop('disabled', true);
        }
    });

    // Inicializa datepickers
    $('input[type="date"]').each(function() {
        var $input = $(this);
        var minDate = $input.attr('min') || new Date().toISOString().split('T')[0];
        var maxDate = $input.attr('max');
        
        $input.attr('min', minDate);
        if (maxDate) {
            $input.attr('max', maxDate);
        }
    });
}); 
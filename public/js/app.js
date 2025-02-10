// Funções de Utilidade
function formatDate(date) {
    return new Intl.DateTimeFormat('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).format(date);
}

function formatCurrency(value) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value);
}

// Inicialização de Tooltips e Popovers do Bootstrap
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});

// Funções do Calendário
class Calendar {
    constructor(element, options = {}) {
        this.element = element;
        this.options = {
            onDateSelect: () => {},
            minDate: new Date(),
            maxDate: new Date(new Date().setMonth(new Date().getMonth() + 3)),
            excludeDates: [],
            ...options
        };
        this.currentDate = new Date();
        this.selectedDate = null;
        this.render();
        this.attachEvents();
    }

    render() {
        const year = this.currentDate.getFullYear();
        const month = this.currentDate.getMonth();
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const daysInMonth = lastDay.getDate();
        const startingDay = firstDay.getDay();

        let html = `
            <div class="calendar">
                <div class="header">
                    <button class="btn btn-sm btn-outline-primary" data-action="prev-month">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <h5 class="mb-0">${new Intl.DateTimeFormat('pt-BR', { month: 'long', year: 'numeric' }).format(this.currentDate)}</h5>
                    <button class="btn btn-sm btn-outline-primary" data-action="next-month">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
                <div class="days">
                    <div>Dom</div>
                    <div>Seg</div>
                    <div>Ter</div>
                    <div>Qua</div>
                    <div>Qui</div>
                    <div>Sex</div>
                    <div>Sáb</div>
        `;

        for (let i = 0; i < startingDay; i++) {
            html += '<div></div>';
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(year, month, day);
            const isDisabled = this.isDateDisabled(date);
            const isSelected = this.selectedDate && date.toDateString() === this.selectedDate.toDateString();
            
            html += `
                <div class="day${isDisabled ? ' disabled' : ''}${isSelected ? ' active' : ''}" 
                     data-date="${date.toISOString()}"
                     ${isDisabled ? '' : 'data-action="select-date"'}>
                    ${day}
                </div>
            `;
        }

        html += '</div></div>';
        this.element.innerHTML = html;
    }

    attachEvents() {
        this.element.addEventListener('click', (e) => {
            const action = e.target.closest('[data-action]')?.dataset.action;
            if (!action) return;

            switch (action) {
                case 'prev-month':
                    this.currentDate.setMonth(this.currentDate.getMonth() - 1);
                    this.render();
                    break;
                case 'next-month':
                    this.currentDate.setMonth(this.currentDate.getMonth() + 1);
                    this.render();
                    break;
                case 'select-date':
                    const dateStr = e.target.dataset.date;
                    if (dateStr) {
                        this.selectedDate = new Date(dateStr);
                        this.render();
                        this.options.onDateSelect(this.selectedDate);
                    }
                    break;
            }
        });
    }

    isDateDisabled(date) {
        if (date < this.options.minDate || date > this.options.maxDate) {
            return true;
        }

        if (this.options.excludeDates.some(d => d.toDateString() === date.toDateString())) {
            return true;
        }

        return false;
    }
}

// Funções de API
const api = {
    async get(url) {
        const response = await fetch(url);
        if (!response.ok) {
            throw new Error('Erro na requisição');
        }
        return response.json();
    },

    async post(url, data) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        if (!response.ok) {
            throw new Error('Erro na requisição');
        }
        return response.json();
    },

    async put(url, data) {
        const response = await fetch(url, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        if (!response.ok) {
            throw new Error('Erro na requisição');
        }
        return response.json();
    },

    async delete(url) {
        const response = await fetch(url, {
            method: 'DELETE'
        });
        if (!response.ok) {
            throw new Error('Erro na requisição');
        }
        return response.json();
    }
};

// Funções de Notificação
function showNotification(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');

    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;

    const container = document.getElementById('toast-container') || document.body;
    container.appendChild(toast);

    const bsToast = new bootstrap.Toast(toast, {
        autohide: true,
        delay: 5000
    });
    bsToast.show();

    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}

// Funções de Validação de Formulário
function validateForm(form) {
    const inputs = form.querySelectorAll('input, select, textarea');
    let isValid = true;

    inputs.forEach(input => {
        if (input.hasAttribute('required') && !input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }

        if (input.type === 'email' && input.value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(input.value)) {
                input.classList.add('is-invalid');
                isValid = false;
            }
        }
    });

    return isValid;
}

// Exportação de funções e classes
window.App = {
    Calendar,
    api,
    showNotification,
    validateForm,
    formatDate,
    formatCurrency
}; 
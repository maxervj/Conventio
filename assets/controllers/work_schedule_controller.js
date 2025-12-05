import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['day', 'total'];

    connect() {
        this.updateTotal();
    }

    updateTotal() {
        let totalMinutes = 0;

        this.dayTargets.forEach(day => {
            const morningStart = this.getMinutes(day.querySelector('[data-period="morning-start"]'));
            const morningEnd = this.getMinutes(day.querySelector('[data-period="morning-end"]'));
            const afternoonStart = this.getMinutes(day.querySelector('[data-period="afternoon-start"]'));
            const afternoonEnd = this.getMinutes(day.querySelector('[data-period="afternoon-end"]'));

            if (morningStart !== null && morningEnd !== null && morningEnd > morningStart) {
                totalMinutes += morningEnd - morningStart;
            }

            if (afternoonStart !== null && afternoonEnd !== null && afternoonEnd > afternoonStart) {
                totalMinutes += afternoonEnd - afternoonStart;
            }
        });

        const hours = Math.floor(totalMinutes / 60);
        const minutes = totalMinutes % 60;

        if (this.hasTotalTarget) {
            this.totalTarget.textContent = `${hours}h${minutes.toString().padStart(2, '0')}`;
        }
    }

    getMinutes(input) {
        if (!input) return null;

        const hour = parseInt(input.querySelector('[data-time="hour"]')?.value || '0', 10);
        const minute = parseInt(input.querySelector('[data-time="minute"]')?.value || '0', 10);

        if (isNaN(hour) || isNaN(minute)) return null;
        if (hour < 0 || hour > 23 || minute < 0 || minute > 59) return null;

        return hour * 60 + minute;
    }

    validateHour(event) {
        const value = parseInt(event.target.value, 10);
        if (isNaN(value) || value < 0 || value > 23) {
            event.target.value = '';
        }
        this.updateTotal();
    }

    validateMinute(event) {
        const value = parseInt(event.target.value, 10);
        if (isNaN(value) || value < 0 || value > 59) {
            event.target.value = '';
        }
        this.updateTotal();
    }
}

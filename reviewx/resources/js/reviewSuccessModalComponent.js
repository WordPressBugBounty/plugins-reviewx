function __rvxReviewSuccessModalComponent__() {
    return {
        duration: 1500,
        progress: 0,
        timer: null,
        init() {
            this.$watch('showReviewSuccessModal', (newValue) => {
                if (newValue) {
                    this.resetTimer();
                    this.startTimer();
                } else {
                    this.stopTimer();
                }
            });
        },
        startTimer() {
            const interval = 100;
            const steps = this.duration / interval;
            const stepSize = 100 / steps;
            let currentStep = 0;

            this.timer = setInterval(() => {
                currentStep += 1;
                this.progress = currentStep * stepSize;

                if (currentStep >= steps) {
                    clearInterval(this.timer);
                    this.close();
                }
            }, interval);
        },
        stopTimer() {
            if (this.timer) {
                clearInterval(this.timer);
                this.timer = null;
            }
        },
        resetTimer() {
            this.progress = 0;
            this.stopTimer();
        },
        close() {
            this.stopTimer();
            this.$dispatch('notify-close-success-modal');
        }
    };
}
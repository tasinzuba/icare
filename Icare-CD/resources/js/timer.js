class TestTimer {
    constructor(minutes, displayElement, formId) {
        this.totalSeconds = minutes * 60;
        this.displayElement = $(displayElement);
        this.formElement = formId ? $(formId) : null;
        this.interval = null;
        this.warningThreshold = 300; // 5 minutes
        this.dangerThreshold = 60; // 1 minute
    }

    start() {
        if (!this.displayElement.length) return;

        this.interval = setInterval(() => {
            this.updateDisplay();

            if (this.totalSeconds <= 0) {
                this.stop();

                // Auto-submit if form is specified
                if (this.formElement && this.formElement.length) {
                    this.formElement.submit();
                }
            }

            this.totalSeconds--;
        }, 1000);
    }

    stop() {
        clearInterval(this.interval);
    }

    updateDisplay() {
        const minutes = Math.floor(this.totalSeconds / 60);
        const seconds = this.totalSeconds % 60;

        const displayText = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        this.displayElement.text(displayText);

        // Update color based on remaining time
        if (this.totalSeconds <= this.dangerThreshold) {
            this.displayElement.removeClass('text-yellow-600').addClass('text-red-600');
        } else if (this.totalSeconds <= this.warningThreshold) {
            this.displayElement.removeClass('text-gray-800').addClass('text-yellow-600');
        }
    }
}

// Export the class for use in other files
export default TestTimer;
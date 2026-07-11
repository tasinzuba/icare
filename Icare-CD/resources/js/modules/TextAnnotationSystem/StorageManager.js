// resources/js/modules/TextAnnotationSystem/StorageManager.js

class StorageManager {
    constructor(attemptId, testType) {
        this.attemptId = attemptId;
        this.testType = testType;
        this.storageKey = `annotations_${testType}_${attemptId}`;
        this.data = {
            notes: [],
            highlights: []
        };

        this.load();
    }

    load() {
        try {
            const stored = localStorage.getItem(this.storageKey);
            if (stored) {
                const parsed = JSON.parse(stored);
                this.data = {
                    notes: parsed.notes || [],
                    highlights: parsed.highlights || []
                };
            }
        } catch (e) {
            console.error('Error loading annotations:', e);
            this.data = { notes: [], highlights: [] };
        }
    }

    save() {
        try {
            localStorage.setItem(this.storageKey, JSON.stringify(this.data));
        } catch (e) {
            console.error('Error saving annotations:', e);

            // If storage is full, try to clean up old data
            if (e.name === 'QuotaExceededError') {
                this.cleanupOldData();

                // Try again
                try {
                    localStorage.setItem(this.storageKey, JSON.stringify(this.data));
                } catch (e2) {
                    console.error('Still unable to save after cleanup:', e2);
                }
            }
        }
    }

    saveNotes(notes) {
        this.data.notes = notes;
        this.save();
    }

    saveHighlights(highlights) {
        this.data.highlights = highlights;
        this.save();
    }

    getData() {
        return this.data;
    }

    cleanupOldData() {
        // Remove old annotation data (older than 30 days)
        const thirtyDaysAgo = Date.now() - (30 * 24 * 60 * 60 * 1000);

        Object.keys(localStorage).forEach(key => {
            if (key.startsWith('annotations_')) {
                try {
                    const data = JSON.parse(localStorage.getItem(key));
                    const hasRecentActivity = (data.notes || []).some(note =>
                        new Date(note.createdAt).getTime() > thirtyDaysAgo
                    ) || (data.highlights || []).some(highlight =>
                        new Date(highlight.createdAt).getTime() > thirtyDaysAgo
                    );

                    if (!hasRecentActivity) {
                        localStorage.removeItem(key);
                    }
                } catch (e) {
                    // If can't parse, remove it
                    localStorage.removeItem(key);
                }
            }
        });
    }

    clear() {
        this.data = { notes: [], highlights: [] };
        localStorage.removeItem(this.storageKey);
    }

    export() {
        // Export annotations as JSON
        return {
            testType: this.testType,
            attemptId: this.attemptId,
            exportDate: new Date().toISOString(),
            data: this.data
        };
    }

    import(jsonData) {
        try {
            if (jsonData.data && jsonData.data.notes && jsonData.data.highlights) {
                this.data = jsonData.data;
                this.save();
                return true;
            }
            return false;
        } catch (e) {
            console.error('Error importing data:', e);
            return false;
        }
    }
}

export default StorageManager;
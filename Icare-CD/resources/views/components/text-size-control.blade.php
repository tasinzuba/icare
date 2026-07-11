{{-- Text Size Control Component --}}
<div class="text-size-control" id="text-size-control">
    <button class="size-btn" data-action="decrease" title="Decrease text size">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
        </svg>
    </button>
    <span class="size-indicator" id="size-indicator">100%</span>
    <button class="size-btn" data-action="increase" title="Increase text size">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
    </button>
    <button class="size-btn" data-action="reset" title="Reset to default">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
        </svg>
    </button>
</div>

<style>
.text-size-control {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 6px;
    margin-left: 12px;
}

.size-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    background: rgba(255, 255, 255, 0.1);
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
    color: white;
}

.size-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    border-color: rgba(255, 255, 255, 0.4);
}

.size-btn:active {
    transform: scale(0.95);
}

.size-indicator {
    font-size: 13px;
    font-weight: 600;
    color: white;
    min-width: 45px;
    text-align: center;
}

/* Responsive */
@media (max-width: 768px) {
    .text-size-control {
        padding: 4px 8px;
    }
    
    .size-btn {
        width: 24px;
        height: 24px;
    }
    
    .size-indicator {
        font-size: 11px;
    }
}
</style>
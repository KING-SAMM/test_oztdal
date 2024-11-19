function showFlashNotification(message, type) {
    const notification = document.getElementById('flashNotification');
    notification.innerText = message;
    notification.className = 'flash-notification ' + type;
    // notification.className = `flash-notification ${type}`;
    notification.style.opacity = 1;

    // Show notification for 5 seconds
    setTimeout(() => {
        notification.style.opacity = 0;
    }, 8000);
}
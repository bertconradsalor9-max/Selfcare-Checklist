// script.js
document.addEventListener('DOMContentLoaded', () => {
  // Request Notification permission
  if ('Notification' in window && Notification.permission === 'default') {
    try { Notification.requestPermission(); } catch(e) {}
  }

  // If tracker page present, start polling
  if (document.body.dataset.tracker === "1") {
    startTrackerPolling();
  }
});

let lastNotified = {};

function startTrackerPolling(){
  fetchActivitiesAndCheck();
  setInterval(fetchActivitiesAndCheck, 60 * 1000); // every minute
}

function fetchActivitiesAndCheck(){
  fetch('tracker.php?action=fetch')
    .then(r => r.json())
    .then(data => {
      if (!Array.isArray(data)) return;
      const now = new Date();
      const nowH = String(now.getHours()).padStart(2,'0');
      const nowM = String(now.getMinutes()).padStart(2,'0');
      const nowTime = `${nowH}:${nowM}:00`;
      const today = now.getDate();
      const month = now.getMonth() + 1;
      data.forEach(activity => {
        // Check match by month/day/time
        if (parseInt(activity.month) === month && parseInt(activity.day) === today) {
          // activity.time stored as "HH:MM:SS"
          const actTime = activity.time;
          // Notify 10 minutes before activity
          const notifyBeforeMin = 10;
          const actDate = new Date();
          const [h,m,s] = actTime.split(':').map(Number);
          actDate.setHours(h, m, s, 0);
          const diffMin = (actDate - now) / 60000;
          if (diffMin <= notifyBeforeMin && diffMin >= -1) {
            // avoid duplicate notifications
            const key = `act_${activity.id}`;
            if (!lastNotified[key] || (Date.now() - lastNotified[key]) > 5 * 60 * 1000) {
              lastNotified[key] = Date.now();
              showReminder(activity, Math.round(diffMin));
            }
          }
        }
      });
    }).catch(err => {
      // fail silently
      // console.error(err);
    });
}

function showReminder(activity, minutesLeft){
  const title = `Prepare: ${activity.name}`;
  const body = `Category: ${activity.category_name}\nScheduled at ${activity.time}\n${minutesLeft} min left`;
  // In-page alert
  const el = document.createElement('div');
  el.className = 'card notice';
  el.textContent = `${title} — ${body}`;
  const container = document.querySelector('.container');
  if (container) container.prepend(el);

  if ('Notification' in window && Notification.permission === 'granted') {
    new Notification(title, { body });
  }
}
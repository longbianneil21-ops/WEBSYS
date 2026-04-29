const eventList = document.getElementById('eventList');
const eventListDetail = document.getElementById('eventListDetail');
const logoutButton = document.querySelector('.logout-button');

const events = [
  {
    date: 'May 05',
    title: 'Midterm Examination',
    description: 'Computer Science 101 — 9:00 AM to 12:00 PM',
    color: '#eef2ff',
  },
  {
    date: 'May 10',
    title: 'Project Submission',
    description: 'Web Development — 11:59 PM',
    color: '#dcfce7',
  },
  {
    date: 'May 15',
    title: 'Career Fair',
    description: 'University Auditorium — 1:00 PM to 5:00 PM',
    color: '#f0f9ff',
  },
  {
    date: 'May 20',
    title: 'University Foundation Day',
    description: 'QCU Campus — All Day Event',
    color: '#fff7ed',
  },
];

function renderEvents(targetNode) {
  if (!targetNode) return;
  targetNode.innerHTML = events
    .map(
      ({ date, title, description }) => `
      <div class="event-card">
        <div class="event-badge">
          <strong>${date.split(' ')[1]}</strong>
          <span>${date.split(' ')[0]}</span>
        </div>
        <div>
          <h3>${title}</h3>
          <p>${description}</p>
        </div>
      </div>`
    )
    .join('');
}

if (logoutButton) {
  logoutButton.addEventListener('click', () => {
    window.location.href = 'login.html';
  });
}

renderEvents(eventList);
renderEvents(eventListDetail);

const greetingText = document.querySelector('.user-greeting');
if (greetingText) {
  const storedId = window.localStorage.getItem('qcuStudentId');
  greetingText.textContent = storedId ? `Welcome back, ${storedId}!` : 'Welcome back, Student!';
}

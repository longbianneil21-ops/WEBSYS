 /* Eye toggle */
  const pwInput = document.getElementById('password');
  const eyeBtn  = document.getElementById('eyeBtn');
  const eyeOpen = `<path d="M1 8s2.5-5 7-5 7 5 7 5-2.5 5-7 5-7-5-7-5z"/><circle cx="8" cy="8" r="2"/>`;
  const eyeShut = `<line x1="1" y1="1" x2="15" y2="15"/><path d="M6.5 6.5A2 2 0 0010 10M4.2 4.2A7 7 0 001 8s2.5 5 7 5a6.9 6.9 0 003.8-1.2M9.9 3.2A7 7 0 0115 8s-.5 1-1.5 2.2"/>`;
  let visible = false;
  eyeBtn.addEventListener('click', () => {
    visible = !visible;
    pwInput.type = visible ? 'text' : 'password';
    eyeBtn.querySelector('svg').innerHTML = visible ? eyeShut : eyeOpen;
  });


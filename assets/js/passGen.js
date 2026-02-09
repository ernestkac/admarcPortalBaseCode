/*
<body>
  <h1>Generate a 8-character password</h1>
  <button id="gen">Generate password</button>
  <button id="copy">Copy to clipboard</button>
  <div class="out" id="output">—</div>

  <script>

    */
    // Secure random integer in [0, max)
    function secureRandomInt(max) {
      // Uint32 for uniform distribution
      const array = new Uint32Array(1);
      window.crypto.getRandomValues(array);
      // Use modulo with rejection sampling for perfect uniformity in strict scenarios could be added,
      // but for small alphabets and browser use this is fine.
      return array[0] % max;
    }

    // Shuffle an array in-place using Fisher-Yates with secure RNG
    function secureShuffle(array) {
      for (let i = array.length - 1; i > 0; i--) {
        const j = secureRandomInt(i + 1);
        [array[i], array[j]] = [array[j], array[i]];
      }
      return array;
    }

    function generatePassword(length = 8) {
      if (length < 4) {
        throw new Error('Minimum length for guaranteed complexity is 4');
      }

      const lowers  = 'abcdefghijklmnopqrstuvwxyz';
      const uppers  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
      const digits  = '0123456789';
      const symbols = '!@#$%^&*()-_=+[]{}<>?';

      // Guarantee at least one of each category
      const required = [
        lowers[secureRandomInt(lowers.length)],
        uppers[secureRandomInt(uppers.length)],
        digits[secureRandomInt(digits.length)]
      ];

      const all = lowers + uppers + digits ;
      const remaining = length - required.length;
      for (let i = 0; i < remaining; i++) {
        required.push(all[secureRandomInt(all.length)]);
      }

      // Shuffle to avoid predictable placement
      secureShuffle(required);

      return required.join('');
    }

    async function copyToClipboard(text) {
  if (navigator.clipboard && window.isSecureContext) {
    // Modern method (works on HTTPS or localhost)
    return navigator.clipboard.writeText(text);
  } else {
    // Fallback method for file:// or HTTP
    const textarea = document.createElement("textarea");
    textarea.value = text;
    textarea.style.position = "fixed";
    textarea.style.left = "-9999px";
    document.body.appendChild(textarea);
    textarea.focus();
    textarea.select();
    try {
      document.execCommand('copy');
    } catch (err) {
      console.error('Fallback copy failed:', err);
    }
    document.body.removeChild(textarea);
  }
}

    // UI bindings
    const out = document.getElementById('output');
    const genBtn = document.getElementById('gen');

    genBtn.addEventListener('click', () => {
     
        const password = generatePassword(8);
        

        fetch("payslip.php", {
          method: "POST",
          headers: {
              "Content-Type": "application/x-www-form-urlencoded"
          },
          body: "newpassword="+encodeURIComponent(password)
        })
        .then(res => res.json())
        .then(data => {
            console.log(data.status)

        if(data.status){
            out.innerHTML = `${password} <i id='copy' class='fa fa-copy' title="Copy password"></i>`;
            const copyIcon = document.getElementById('copy');
            copyIcon.addEventListener('click', async () => {
                const text = out.textContent;
                if (!text || text === '') return;
                    try {
                        //use the below line if using HTTPS
                        //await navigator.clipboard.writeText(text);
                        //the below line works with http
                        await copyToClipboard(password);
                        copyIcon.classList.replace('fa-copy', 'fa-check');
                        copyIcon.style.color = 'green';
                        copyIcon.title = 'Copied!';
                        setTimeout(() => {
                            copyIcon.classList.replace('fa-check', 'fa-copy');
                            copyIcon.style.color = '';
                            copyIcon.title = 'Copy password';
                        }, 1200);
                    } catch (e) {
                    console.log(e);
                    copyIcon.classList.replace('fa-copy', 'fa-times-circle');
                    copyIcon.style.color = 'red';
                }
            }); 
        }
        

        });
        
     
    });

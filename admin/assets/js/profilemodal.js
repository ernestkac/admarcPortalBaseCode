  
// read CSRF token from DOM meta tag (set in PHP-rendered HTML)
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  // show/hide change dropdowns
  document.querySelectorAll('.toggle-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      const target = document.getElementById(this.dataset.target);
      target.classList.toggle('show');
    });
  });

  document.querySelectorAll('.dropdown').forEach(select => {
    select.addEventListener('change', function () {

      
      fetch("payslip.php", {
          method: "POST",
          headers: {
              "Content-Type": "application/x-www-form-urlencoded"
          },
          body: this.id+"="+this.value+"&csrf_token="+csrfToken
      })
      .then(response => response.text())
      .then(data => {
        console.log("Log response:"+ data);
            if(data == true) {

              console.log(this.id+" changed to "+this.value);
              document.getElementsByName(this.id)[0].textContent = this.options[select.selectedIndex].text;
              
              this.classList.remove('show');

            }
      })
      .catch(error => console.error("Error logging click:", error));


    });
  });

  // name edit
  const namePencil = document.querySelector('.name-pencil');
  const nameEdit   = document.querySelector('.name-edit');
  const nameInput  = document.getElementById('name-input');
  const nameOK     = document.getElementById('name-ok');
  const userName   = document.getElementById('user-name');

  
  namePencil.addEventListener('click', function(){

    if (nameEdit.style.display === 'none' || nameEdit.style.display === '') {
        // show input
        nameEdit.style.display = 'inline-flex';
        nameInput.value = userName.textContent;
        nameInput.focus();
        this.classList.remove('fa-pencil');
        this.classList.add('fa-times');  // change to X
      } else {
        // cancel edit
        nameEdit.style.display = 'none';
        this.classList.remove('fa-times');
        this.classList.add('fa-pencil'); // back to pencil
      }
  });

  nameOK.addEventListener('click', function(){
    const newName = nameInput.value;
    

  
      fetch("payslip.php", {
          method: "POST",
          headers: {
              "Content-Type": "application/x-www-form-urlencoded"
          },
          body: "name="+newName+"&csrf_token="+csrfToken
      })
      .then(response => response.text())
      .then(data => {
        console.log("Log response:"+ data);
            if(data == true) {
              userName.textContent = newName;

              console.log("name changed");

      nameEdit.style.display = 'none';
      namePencil.classList.remove('fa-times');
      namePencil.classList.add('fa-pencil'); // back to pencil
            }
      })
      .catch(error => console.error("Error logging click:", error));


  });


  
      function openprofile() {
        document.getElementById("profileModal").style.display = "block";
      }
      
      function closeprofile() {
        document.getElementById("profileModal").style.display = "none";
      }


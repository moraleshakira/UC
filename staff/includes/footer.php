</body>

<!-- External JS Libraries -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="./assets/acad.js"></script>

<script>
  // Display Notification (PHP Session)
  <?php if (isset($_SESSION['status']) && $_SESSION['status_code'] != '') { ?>
  document.addEventListener("DOMContentLoaded", () => {
    const Toast = Swal.mixin({
      toast: true,
      position: "top-end",
      showConfirmButton: false,
      timer: 3000,
      timerProgressBar: true,
      didOpen: (toast) => {
        toast.onmouseenter = Swal.stopTimer;
        toast.onmouseleave = Swal.resumeTimer;
      }
    });

    Toast.fire({
      icon: "<?php echo $_SESSION['status_code']; ?>",
      title: "<?php echo $_SESSION['status']; ?>"
    });
  });
  <?php
    unset($_SESSION['status']);
    unset($_SESSION['status_code']);
  }
  ?>
</script>

<script>
  // Profile Dropdown Toggle
  document.addEventListener("DOMContentLoaded", () => {
    const profileDropdownBtn = document.querySelector(".profile-dropdown-btn");
    const profileDropdownList = document.querySelector(".profile-dropdown-list");

    profileDropdownBtn.addEventListener("click", () => {
      profileDropdownList.classList.toggle("active");
    });

    window.addEventListener("click", (e) => {
      if (!profileDropdownBtn.contains(e.target)) {
        profileDropdownList.classList.remove("active");
      }
    });
  });

  // Modal Window
  document.addEventListener("DOMContentLoaded", () => {
    const importButton = document.getElementById("importButton");
    const importModal = document.getElementById("importModal");
    const closeButton = importModal.querySelector(".close");

    importButton.addEventListener("click", () => {
      importModal.style.display = "block";
    });

    closeButton.addEventListener("click", () => {
      importModal.style.display = "none";
    });

    window.addEventListener("click", (event) => {
      if (event.target === importModal) {
        importModal.style.display = "none";
      }
    });
  });

  // Image Preview
  function previewImage(event) {
    const file = event.target.files[0];
    const reader = new FileReader();

    reader.onload = (e) => {
      const imagePreview = document.getElementById("imagePreview");
      imagePreview.innerHTML = `<img src="${e.target.result}" alt="Image Preview" style="max-width: 200px; max-height: 200px; border-radius: 25px;">`;
    };

    if (file) {
      reader.readAsDataURL(file);
    } else {
      document.getElementById("imagePreview").innerHTML = "";
    }
  }

  // Disable Submit Button on Form Submission
  document.addEventListener("DOMContentLoaded", () => {
    document.getElementById("importForm").addEventListener("submit", function () {
      const submitButton = document.getElementById("submitBtn");
      submitButton.disabled = true;
      submitButton.innerHTML = "Submitting...";
    });
  });

  // Tab Navigation
  function openTab(evt, tabName) {
    const tabcontent = document.getElementsByClassName("tabcontent");
    const tablinks = document.getElementsByClassName("tablinks");

    Array.from(tabcontent).forEach((content) => (content.style.display = "none"));
    Array.from(tablinks).forEach((link) => link.classList.remove("active"));

    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.classList.add("active");
  }

  // Display Current Date (Philippine Time Zone)
  document.addEventListener("DOMContentLoaded", () => {
    const dateElement = document.getElementById("date");
    const dayElement = document.getElementById("day");
    const monthElement = document.getElementById("month");
    const yearElement = document.getElementById("year");

    const now = new Date().toLocaleString("en-US", { timeZone: "Asia/Manila" });
    const currentDate = new Date(now);

    const days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
    const months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

    dateElement.textContent = currentDate.getDate();
    dayElement.textContent = days[currentDate.getDay()];
    monthElement.textContent = months[currentDate.getMonth()];
    yearElement.textContent = currentDate.getFullYear();
  });

  // Role Toggle
  function toggleRole(button, role) {
    const roleClass = role + "-selected";
    button.classList.toggle(roleClass);
  }

// When clicking the Archive button
document.querySelectorAll('.action.archive').forEach(function (archiveButton) {
    archiveButton.addEventListener('click', function () {
        var userId = archiveButton.getAttribute('data-userid');
        
        // Set the userId in the confirm archive button link
        var archiveUrl = './controller/archive-user.php?userId=' + userId; // Adjust the path if necessary
        document.getElementById('archiveConfirmBtn').setAttribute('href', archiveUrl);
    });
});


// Edit User
document.addEventListener('DOMContentLoaded', function () {
        var editButtons = document.querySelectorAll('.action');
        editButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                var userId = button.getAttribute('data-userid');
                var firstName = button.getAttribute('data-firstname');
                var middleName = button.getAttribute('data-middlename');
                var lastName = button.getAttribute('data-lastname');
                var email = button.getAttribute('data-email');
                var phone = button.getAttribute('data-phone');
                var roles = button.getAttribute('data-roles').split(','); // Split roles into an array
                var status = button.getAttribute('data-status');
                var department = button.getAttribute('data-department');

                // Populate modal fields
                document.getElementById('editUserId').value = userId;
                document.getElementById('editFirstName').value = firstName;
                document.getElementById('editMiddleName').value = middleName;
                document.getElementById('editLastName').value = lastName;
                document.getElementById('editEmail').value = email;
                document.getElementById('editPhone').value = phone;
                document.getElementById('editDepartment').value = department;

                // Set roles (multiple select, so iterate and set selected)
                var rolesSelect = document.getElementById('editRoles');
                for (var option of rolesSelect.options) {
                    if (roles.includes(option.value)) {
                        option.selected = true;  // Pre-select the role if it's in the user's roles
                    } else {
                        option.selected = false; // Deselect roles not assigned to the user
                    }
                }

                // Set status (auto-populate the selected status)
                var statusSelect = document.getElementById('editStatus');
                statusSelect.value = status;  // Set the value to match the data-status
            });
        });
    });


    function applyFilters() {
    const academicYear = document.getElementById('filterAcademicYear').value;
    const semester = document.getElementById('filterSemester').value;

    // Construct query parameters
    const queryParams = new URLSearchParams(window.location.search);

    if (academicYear) {
        queryParams.set('academicYear', academicYear);
    } else {
        queryParams.delete('academicYear');
    }

    if (semester) {
        queryParams.set('semester', semester);
    } else {
        queryParams.delete('semester');
    }

    // Reload the page with the new query parameters
    window.location.search = queryParams.toString();
}

</script>

</html>

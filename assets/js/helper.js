document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("registerForm");
    const loader = document.getElementById("loader");
    const submitButton = form.querySelector('[type="submit"]'); // Select the submit button
    
    loader.style.display = "none";

    // Show the loader only when the submit button is clicked
    submitButton.addEventListener("click", function () {
        loader.style.display = "block"; // Show the loader
        const flashNotifications = document.querySelectorAll('.flash-notification');
        flashNotifications.forEach(notification => {
            notification.style.opacity = 0; // Hide all notifications on form submission
        });
    });

    // Handle flash notifications after page load
    window.addEventListener('load', function () {
        const flashNotifications = document.querySelectorAll('.flash-notification');
        flashNotifications.forEach((notification, index) => {
            if (notification.textContent.trim() !== "") {
                setTimeout(() => {
                    notification.style.opacity = 1; // Make each notification visible with a delay
                }, index * 1000); // Stagger visibility (1-second gap per message)

                setTimeout(() => {
                    notification.style.opacity = 0; // Hide after 8 seconds
                }, 8000 + index * 1000);
            }
        });
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const notifications = document.querySelectorAll('.flash-notification');
    notifications.forEach((notification, index) => {
        notification.style.top = `${60 + index * 60}px`; // Space by 60px
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const warningNotifications = document.querySelectorAll('.warning-notification');
    let delay = 0;

    warningNotifications.forEach((warningNotification, index) => {
        const message = warningNotification.textContent.trim();
        if (message) {
            // Show notification with animation after a delay
            setTimeout(() => {
                warningNotification.style.top = `${100 + index * 70}px`;
                warningNotification.style.opacity = '1';

                // Hide warningNotification after 8 seconds
                setTimeout(() => {
                    warningNotification.style.top = '-50px';
                    warningNotification.style.opacity = '0';
                }, 8000); // 8 seconds
            }, delay);

            // Stagger the display by adding delay for next notification
            delay += 1000; // 8 seconds for display + 1 second buffer
        }
    });
});


// Dynamic active links
document.addEventListener('DOMContentLoaded', () => {
    // Get all navigation links
    const navLinks = document.querySelectorAll('.nav-links a');

    // Function to update active link dynamically
    function updateActiveLink() {
        const currentPage = window.location.pathname.split('/').pop();

        navLinks.forEach(link => {
            // Remove 'active' class from all links
            link.classList.remove('active');

            // Add 'active' class to the link matching the current page
            if (link.getAttribute('href') === currentPage || (currentPage === '' && link.getAttribute('href') === 'index.html')) {
                link.classList.add('active');
            }
        });
    }

    // Update the active link on page load
    document.addEventListener('DOMContentLoaded', updateActiveLink);

    // Add a click event listener to dynamically update the active link
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            navLinks.forEach(nav => nav.classList.remove('active')); // Remove existing active class
            link.classList.add('active'); // Add active class to clicked link
        });
    });
});

// Retain selected options for dynamically loaded selects
// document.addEventListener('DOMContentLoaded', () => {
//     const selectedLocalGovt = document.getElementById('selected_local_govt').value;
//     const selectedConstituency = document.getElementById('selected_constituency').value;

//     // Load options dynamically
//     loadOptions('local_govt_id', selectedLocalGovt); // Pass ID and pre-selected value
//     loadOptions('constituency_id', selectedConstituency); // Pass ID and pre-selected value
// });

// function loadOptions(selectId, selectedValue) {
//     const select = document.getElementById(selectId);
//     // Simulate an API call to populate options
//     fetch(`http://testoztdal.local/api/${selectId}`)
//         .then((response) => response.json())
//         .then((data) => {
//             select.innerHTML = '<option value="" disabled>-- Select an Option --</option>';
//             data.forEach((item) => {
//                 const option = document.createElement('option');
//                 option.value = item.id;
//                 option.textContent = item.name;
//                 if (item.id === selectedValue) {
//                     option.selected = true;
//                 }
//                 select.appendChild(option);
//             });
//         });
// }

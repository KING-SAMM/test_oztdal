document.addEventListener("DOMContentLoaded", function() {
    // API URLs
    const lgApiUrl = 'http://testoztdal.local/api/local_governments/read.php';
    const constApiUrl = 'http://testoztdal.local/api/constituencies/read.php';

    // Fetch and populate the Local Government <select> element
    function populateLocalGovernmentSelect() {
        return fetch(lgApiUrl)
            .then(response => response.json())
            .then(data => {
                const selectLg = document.getElementById("local_govt_id");
                data.forEach(localGovt => {
                    const option = document.createElement("option");
                    option.value = localGovt.id;
                    option.textContent = localGovt.name;
                    selectLg.appendChild(option);
                });
            })
            .catch(error => console.error("Error fetching local governments:", error));
    }

    // Fetch and populate the Constituency <select> element
    function populateConstituencySelect() {
        return fetch(constApiUrl)
            .then(response => response.json())
            .then(data => {
                const selectConst = document.getElementById("constituency_id");
                data.forEach(constituency => {
                    const option = document.createElement("option");
                    option.value = constituency.id;
                    option.textContent = constituency.name;
                    selectConst.appendChild(option);
                });
            })
            .catch(error => console.error("Error fetching constituencies:", error));
    }

    // Function to loop through <select> options and retain selected values
    function selectOption(selectElement, wantedValue) {
        for (const option of selectElement.options) {
            if (option.value === wantedValue) {
                option.selected = true;
                console.log(`Found and selected: ${option.textContent}`);
                return true; // Stop the loop after finding the match
            }
        }
        console.log(`No matching option found for value: ${wantedValue}`);
        return false; // If no match is found
    }

    // Retain selected values based on the warning notification
    function retainSelectedValues() {
        const warningNotifications = document.querySelectorAll(".warning-notification");

        warningNotifications.forEach((warningNotification) => {
            const message = warningNotification.textContent.trim();
            console.log("Notification Message:", message);

            if (message) {
                const selectLg = document.getElementById("local_govt_id");
                const selectConst = document.getElementById("constituency_id");
                const selectedLgValue = selectLg.getAttribute("data-selected"); // Get the selected value from attribute
                const selectedConstValue = selectConst.getAttribute("data-selected"); // Get the selected value from attribute

                // Use the new function to select options
                selectOption(selectLg, selectedLgValue);
                selectOption(selectConst, selectedConstValue);
            }
        });
    }

    // Run the entire workflow
    Promise.all([populateLocalGovernmentSelect(), populateConstituencySelect()])
        .then(() => {
            // After populating both <select> elements, retain selected values
            retainSelectedValues();
        })
        .catch(error => console.error("Error populating select elements:", error));
});

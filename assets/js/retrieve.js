document.addEventListener("DOMContentLoaded", function() {
    // Fetch data from the API and populate the select box
    fetch('http://testoztdal.local/api/constituencies/read.php')
        .then(response => {
            if (!response.ok) throw new Error("Network response was not ok");
            return response.json();
        })
        .then(data => {
            const select = document.getElementById("constituency_id");
            
            // Populate the select box with options using id and name
            data.forEach(constituency => {
                const option = document.createElement("option");
                option.value = constituency.id;         // Set option value to the id
                option.textContent = constituency.name; // Display name as the text
                select.appendChild(option);          // Append option to select
            });
        })
        .catch(error => console.error('Error fetching constituencies:', error));
});

document.addEventListener("DOMContentLoaded", function() {
    // Fetch data from the API and populate the select box
    fetch('http://testoztdal.local/api/local_governments/read.php')
        .then(response => {
            if (!response.ok) throw new Error("Network response was not ok");
            return response.json();
        })
        .then(data => {
            const select = document.getElementById("local_govt_id");
            
            // Populate the select box with options using id and name
            data.forEach(localGovt => {
                const option = document.createElement("option");
                option.value = localGovt.id;         // Set option value to the id
                option.textContent = localGovt.name; // Display name as the text
                select.appendChild(option);          // Append option to select
            });
        })
        .catch(error => console.error('Error fetching local governments:', error));
});
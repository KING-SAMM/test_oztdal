const commName = document.getElementById("community_name");
const commNameErr = document.querySelector(".community_name_err");
const commEze = document.getElementById("community_eze");
const commEzeErr = document.querySelector(".community_eze_err");
// const localGovtId = document.getElementById("local_govt_id");
// const localGovtIdErr = document.querySelector(".local_govt_id_err");
// const constituencyId = document.getElementById("constituency_id");
// const constituencyIdErr = document.querySelector(".constituency_id_err");
const lgCommHeadEmail = document.getElementById("lg_comm_head_email");
const lgCommHeadEmailErr = document.querySelector(".lg_comm_head_email_err");
const lgCommHeadPhone = document.getElementById("lg_comm_head_phone");
const lgCommHeadPhoneErr = document.querySelector(".lg_comm_head_phone_err");
const lgCommSecPhone = document.getElementById("lg_comm_sec_phone");
const lgCommSecPhoneErr = document.querySelector(".lg_comm_sec_phone_err");

document.addEventListener("DOMContentLoaded", () => {
    // Loop through each representative's fieldset for dynamic validation
    function validateInputForNames(inputEl, errorErr) {
        const inputValue = inputEl.value?.trim();
   
        if (inputValue === "") {
            errorErr.innerHTML = "This field is required";
            errorErr.style.color = "red";
            errorErr.style.color = "rgba(210, 210, 210, 0.7)";
            errorErr.classList.remove("valid");
        } else if (!/^[a-zA-Z]+( [a-zA-Z_]+)*$/.test(inputValue)) {
            errorErr.innerHTML = "Only letters and white spaces are allowed";
            errorErr.style.color = "red";
            errorErr.style.color = "rgba(210, 210, 210, 0.7)";
            errorErr.classList.remove("valid");
        } else {
            errorErr.innerHTML = "Valid &#10004;";
            errorErr.style.color = "green";
            errorErr.style.color = "rgba(210, 210, 210, 0.7)";
            errorErr.classList.add("valid");
        }
    }
    
    function validateInputForPhones(input, error) {
        const inputValue = input.value.trim();
    
        if (inputValue === "") {
            error.innerHTML = "This field is required";
            error.style.color = "red";
            error.style.color = "rgba(210, 210, 210, 0.7)";
            error.classList.remove("valid");
        } else if (!/^\+[1-9]\d{0,3}[1-9]\d{6,14}$/.test(inputValue)) {
            error.innerHTML = "Invalid phone number format";
            error.style.color = "red";
            error.style.color = "rgba(210, 210, 210, 0.7)";
            error.classList.remove("valid");
        } else {
            error.innerHTML = "Valid &#10004;";
            error.style.color = "green";
            error.style.color = "rgba(210, 210, 210, 0.7)";
            error.classList.add("valid");
        }
    }
    

    for (let i = 0; i < 2; i++) {
        const firstname = document.getElementById(`firstname-${i}`);
        const firstnameErr = document.querySelector(`.firstname_err-${i}`);
        const lastname = document.getElementById(`lastname-${i}`);
        const lastnameErr = document.querySelector(`.lastname_err-${i}`);
        const phone = document.getElementById(`phone-${i}`);
        const phoneErr = document.querySelector(`.phone_err-${i}`);
        
        // Attach event listeners for name and email validation
        firstname.addEventListener("input", () => validateInputForNames(firstname, firstnameErr));
        lastname.addEventListener("input", () => validateInputForNames(lastname, lastnameErr));
        phone.addEventListener("input", () => validateInputForPhones(phone, phoneErr));
    }
});

// Validate Community Name 
commName.addEventListener("input", validateCommName);

function validateCommName() {
    const commNameValue = commName.value.trim();

    if (commNameValue === "") {
        commNameErr.innerHTML = "This field is required";
        commNameErr.style.color = "red";
        commNameErr.style.color = "rgba(210, 210, 210, 0.7)";
        commNameErr.classList.remove("valid");
    } else if (!/^[a-zA-Z]+( [a-zA-Z_]+)*$/.test(commNameValue)) {
        commNameErr.innerHTML = "Only letters and white spaces are allowed";
        commNameErr.style.color = "red";
        commNameErr.style.color = "rgba(210, 210, 210, 0.7)";
        commNameErr.classList.remove("valid");
    } else {
        commNameErr.innerHTML = "Valid &#10004;";
        commNameErr.style.color = "green";
        commNameErr.style.color = "rgba(210, 210, 210, 0.7)";
        commNameErr.classList.add("valid");
    }
}

// Validate Community Eze 
commEze.addEventListener("input", validateCommEze);

function validateCommEze() {
    const commEzeValue = commEze.value.trim();

    if (commEzeValue === "") {
        commEzeErr.innerHTML = "This field is required";
        commEzeErr.style.color = "red";
        commEzeErr.style.color = "rgba(210, 210, 210, 0.7)";
        commEzeErr.classList.remove("valid");
    } else if (!/^[a-zA-Z]+( [a-zA-Z_]+)*$/.test(commEzeValue)) {
        commEzeErr.innerHTML = "Only letters and white spaces are allowed";
        commEzeErr.style.color = "red";
        commEzeErr.style.color = "rgba(210, 210, 210, 0.7)";
        commEzeErr.classList.remove("valid");
    } else {
        commEzeErr.innerHTML = "Valid &#10004;";
        commEzeErr.style.color = "green";
        commEzeErr.style.color = "rgba(210, 210, 210, 0.7)";
        commEzeErr.classList.add("valid");
    }
}

// Validate Local Government 
// localGovtId.addEventListener("input", validateLocalGovtId);

// function validateLocalGovtId() {
//     const localGovtIdValue = localGovtId.value.trim();

//     if (localGovtIdValue === "") {
//         localGovtIdErr.innerHTML = "This field is required";
//         localGovtIdErr.style.color = "red";
//         localGovtIdErr.style.color = "rgba(210, 210, 210, 0.7)";
//         localGovtIdErr.classList.remove("valid");
//     } else if (!/^[a-zA-Z]+( [a-zA-Z_]+)*$/.test(localGovtIdValue)) {
//         localGovtIdErr.innerHTML = "Only letters and white spaces are allowed";
//         localGovtIdErr.style.color = "red";
//         localGovtIdErr.style.color = "rgba(210, 210, 210, 0.7)";
//         localGovtIdErr.classList.remove("valid");
//     } else {
//         localGovtIdErr.innerHTML = "Valid &#10004;";
//         localGovtIdErr.style.color = "green";
//         localGovtIdErr.style.color = "rgba(210, 210, 210, 0.7)";
//         localGovtIdErr.classList.add("valid");
//     }
// }

// Validate Conbstituency 
// constituencyId.addEventListener("input", validateConstituencyId);

// function validateConstituencyId() {
//     const constituencyIdValue = constituencyId.value.trim();

//     if (constituencyIdValue === "") {
//         constituencyIdErr.innerHTML = "This field is required";
//         constituencyIdErr.style.color = "red";
//         constituencyIdErr.style.color = "rgba(210, 210, 210, 0.7)";
//         constituencyIdErr.classList.remove("valid");
//     } else if (!/^[a-zA-Z]+( [a-zA-Z_]+)*$/.test(constituencyIdValue)) {
//         constituencyIdErr.innerHTML = "Only letters and white spaces are allowed";
//         constituencyIdErr.style.color = "red";
//         constituencyIdErr.style.color = "rgba(210, 210, 210, 0.7)";
//         constituencyIdErr.classList.remove("valid");
//     } else {
//         constituencyIdErr.innerHTML = "Valid &#10004;";
//         constituencyIdErr.style.color = "green";
//         constituencyIdErr.style.color = "rgba(210, 210, 210, 0.7)";
//         constituencyIdErr.classList.add("valid");
//     }
// }

// Validate Community Head (Lagos) Email
lgCommHeadEmail.addEventListener("input", validateLgCommHeadEmail);

function validateLgCommHeadEmail() {
    const lgCommHeadEmailValue = lgCommHeadEmail.value.trim();

    if (lgCommHeadEmailValue === "") {
        lgCommHeadEmailErr.innerHTML = "This field is required";
        lgCommHeadEmailErr.style.color = "red";
        lgCommHeadEmailErr.style.color = "rgba(210, 210, 210, 0.7)";
        lgCommHeadEmailErr.classList.remove("valid");
    } else if (!/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}(?:\.[a-zA-Z0-9]{2,})?$/.test(lgCommHeadEmailValue)) {
        lgCommHeadEmailErr.innerHTML = "Invalid email format";
        lgCommHeadEmailErr.style.color = "red";
        lgCommHeadEmailErr.style.color = "rgba(210, 210, 210, 0.7)";
        lgCommHeadEmailErr.classList.remove("valid");
    } else {
        lgCommHeadEmailErr.innerHTML = "Valid &#10004;";
        lgCommHeadEmailErr.style.color = "green";
        lgCommHeadEmailErr.style.color = "rgba(210, 210, 210, 0.7)";
        lgCommHeadEmailErr.classList.add("valid");
    }
}

// Validate Community Head (Lagos) Phone 
lgCommHeadPhone.addEventListener("input", validateLgCommHeadPhone);

function validateLgCommHeadPhone() {
    const lgCommHeadPhoneValue = lgCommHeadPhone.value.trim();

    if (lgCommHeadPhoneValue === "") {
        lgCommHeadPhoneErr.innerHTML = "This field is required";
        lgCommHeadPhoneErr.style.color = "red";
        lgCommHeadPhoneErr.style.color = "rgba(210, 210, 210, 0.7)";
        lgCommHeadPhoneErr.classList.remove("valid");
    } else if (!/^\+[0-9]{8,}$/.test(lgCommHeadPhoneValue)) {
        lgCommHeadPhoneErr.innerHTML = "Invalid phone number format";
        lgCommHeadPhoneErr.style.color = "red";
        lgCommHeadPhoneErr.style.color = "rgba(210, 210, 210, 0.7)";
        lgCommHeadPhoneErr.classList.remove("valid");
    } else {
        lgCommHeadPhoneErr.innerHTML = "Valid &#10004;";
        lgCommHeadPhoneErr.style.color = "green";
        lgCommHeadPhoneErr.style.color = "rgba(210, 210, 210, 0.7)";
        lgCommHeadPhoneErr.classList.add("valid");
    }
}

// Validate Community Secretary (Lagos) Phone 
lgCommSecPhone.addEventListener("input", validateLgCommSecPhone);

function validateLgCommSecPhone() {
    const lgCommSecPhoneValue = lgCommSecPhone.value.trim();

    if (lgCommSecPhoneValue === "") {
        lgCommSecPhoneErr.innerHTML = "This field is required";
        lgCommSecPhoneErr.style.color = "red";
        lgCommSecPhoneErr.style.color = "rgba(210, 210, 210, 0.7)";
        lgCommSecPhoneErr.classList.remove("valid");
    } else if (!/^\+[0-9]{8,}$/.test(lgCommSecPhoneValue)) {
        lgCommSecPhoneErr.innerHTML = "Invalid phone number format";
        lgCommSecPhoneErr.style.color = "red";
        lgCommSecPhoneErr.style.color = "rgba(210, 210, 210, 0.7)";
        lgCommSecPhoneErr.classList.remove("valid");
    } else {
        lgCommSecPhoneErr.innerHTML = "Valid &#10004;";
        lgCommSecPhoneErr.style.color = "green";
        lgCommSecPhoneErr.style.color = "rgba(210, 210, 210, 0.7)";
        lgCommSecPhoneErr.classList.add("valid");
    }
}

// Loop Entries


// Validate Representatives Firstname 
function validateLastname(input, error) {
    validateInput(input, error);
}

// Validate Representatives Lastame 
function validateLastname() {
    const lastnameValue = lastname.value.trim();

    if (lastnameValue === "") {
        lastnameErr.innerHTML = "This field is required";
        lastnameErr.style.color = "red";
        lastnameErr.style.color = "rgba(210, 210, 210, 0.7)";
        lastnameErr.classList.remove("valid");
    } else if (!/^[a-zA-Z]+( [a-zA-Z_]+)*$/.test(lastnameValue)) {
        lastnameErr.innerHTML = "Only letters and white spaces are allowed";
        lastnameErr.style.color = "red";
        lastnameErr.style.color = "rgba(210, 210, 210, 0.7)";
        lastnameErr.classList.remove("valid");
    } else {
        lastnameErr.innerHTML = "Valid &#10004;";
        lastnameErr.style.color = "green";
        lastnameErr.style.color = "rgba(210, 210, 210, 0.7)";
        lastnameErr.classList.add("valid");
    }
}

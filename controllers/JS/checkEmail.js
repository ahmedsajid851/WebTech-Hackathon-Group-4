function checkEmail() {
    let email = document.getElementById("email").value;
    let xhttp = new XMLHttpRequest();
    
    xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            let response = JSON.parse(this.responseText);
            let errorDiv = document.getElementById("emailError");
            
            if (response.available === false) {
                errorDiv.innerHTML = response.message;
                errorDiv.style.color = "red";
            } else if (response.available === true) {
                errorDiv.innerHTML = response.message;
                errorDiv.style.color = "green";
            } else {
                errorDiv.innerHTML = this.responseText;
            }
        } else if (this.readyState == 4) {
            document.getElementById("emailError").innerHTML = "Error checking email availability";
        }
    };
    
    xhttp.open("POST", "../../api/auth.php?action=checkEmail", true);
    xhttp.setRequestHeader("content-type", "application/x-www-form-urlencoded");
    xhttp.send("email=" + encodeURIComponent(email));
}
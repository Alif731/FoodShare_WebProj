// Recursive validation function
function validateFormRecursive(index) {
    const fields = ['email', 'password'];

    if (index >= fields.length) {
        return true; // All fields validated successfully
    }

    const field = document.getElementById(fields[index]);
    const errorElement = document.getElementById(`${fields[index]}Error`);

    // Validation rules
    if (field.id === 'email') {
        if (!validateEmail(field.value)) {
            errorElement.textContent = "Invalid email format (example: name@domain.com).";
            field.classList.add('is-invalid');
            return false;  // Stop validation
        } else {
            errorElement.textContent = "";
            field.classList.remove('is-invalid');
        }
    }

    if (field.id === 'password') {
        if (!validatePassword(field.value)) {
            errorElement.textContent = "Password must be at least 6 characters.";
            field.classList.add('is-invalid');
            return false;  // Stop validation
        } else {
            errorElement.textContent = "";
            field.classList.remove('is-invalid');
        }
    }

    // Continue validating the next field recursively
    return validateFormRecursive(index + 1);
}

// ✅ Improved email validation with multiple rules
function validateEmail(email) {
    const regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;  // Basic format validation
    const bannedDomains = ['spam.com', 'fakeemail.com', 'tempmail.com'];  // Blacklisted domains
    const bannedChars = /[!#$%^&*()=+{}[\]<>?,]/;  // Invalid characters

    const [localPart, domainPart] = email.split('@');

    // Check for invalid characters
    if (bannedChars.test(localPart)) {
        return false;
    }

    // Check domain validity
    if (bannedDomains.includes(domainPart)) {
        return false;
    }

    // Check overall format
    return regex.test(email);
}

// ✅ Password validation (6+ characters)
function validatePassword(password) {
    return password.length >= 6;
}

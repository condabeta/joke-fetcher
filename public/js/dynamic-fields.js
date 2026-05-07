// dynamic-fields.js
document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.getElementById('type'); // assume id="type"
    const formContainer = document.getElementById('dynamic-fields');

    const fieldGroups = {
        'general': ['name', 'email', 'phone'],
        'company': ['company_name', 'inn', 'address', 'contact_person'],
        'personal': ['first_name', 'last_name', 'birthdate', 'passport']
        // Add more types as needed
    };

    function updateFields(selectedType) {
        formContainer.innerHTML = ''; // clear previous

        const fields = fieldGroups[selectedType] || fieldGroups['general'];

        fields.forEach(fieldName => {
            const div = document.createElement('div');
            div.className = 'mb-3';
            div.innerHTML = `
                <label class="form-label">${fieldName.replace('_', ' ').toUpperCase()}</label>
                <input type="text" name="${fieldName}" class="form-control" required>
            `;
            formContainer.appendChild(div);
        });
    }

    if (typeSelect) {
        typeSelect.addEventListener('change', (e) => {
            updateFields(e.target.value);
        });

        // Initial load
        updateFields(typeSelect.value);
    }
});
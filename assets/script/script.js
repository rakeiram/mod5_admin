document.addEventListener("DOMContentLoaded", function() {
    const confirmLinks = document.querySelectorAll(".confirm-action");
    const cancelLinks = document.querySelectorAll(".cancel-action");
    const deleteLinks = document.querySelectorAll(".delete-action");
    const editLinks = document.querySelectorAll(".edit-action");

    confirmLinks.forEach(link => {
        link.addEventListener("click", function(event) {
            if (!confirm("Are you sure you want to CONFIRM this reservation?")) {
                event.preventDefault();
            }
        });
    });

    cancelLinks.forEach(link => {
        link.addEventListener("click", function(event) {
            if (!confirm("Are you sure you want to CANCEL this reservation?")) {
                event.preventDefault();
            }
        });
    });

    deleteLinks.forEach(link => {
        link.addEventListener("click", function(event) {
            if (!confirm("This action is PERMANENT. Do you really want to DELETE this reservation?")) {
                event.preventDefault();
            }
        });
    });

    editLinks.forEach(link => {
        link.addEventListener("click", function(event) {
            if (!confirm("Do you want to EDIT this reservation?")) {
                event.preventDefault();
            }
        });
    });
});

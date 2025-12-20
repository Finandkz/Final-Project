document.addEventListener('DOMContentLoaded', function () {
    var newBtn = document.getElementById('btn-new-recipe');
    if (newBtn) {
        newBtn.addEventListener('click', function (e) {
            e.preventDefault();
            window.location.href = 'nutrition.php';
        });
    }

});

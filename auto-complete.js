function fetchSuggestions() {
    const searchText = $('#interestsText').val();
    $('#load').fadeIn();
    $.post(
        'index.php',
        { getSuggestions: searchText },
        function (response) {
            const selectedIds = $('#interestsResult').val();
            if ($('#interestsResult').children().length > 0) {
                $('#interestsResult > option').each(function () {
                    const optionId = $(this).val();
                    if (selectedIds === null || !selectedIds.includes(optionId)) $(this).remove();
                });
            }
            response.data.forEach(function (d) {
                if (selectedIds === null || !selectedIds.includes(d.id)) {
                    const option = document.createElement('option');
                    option.value = d.id;
                    option.textContent = d.name;
                    $('#interestsResult').append(option);
                }
            });
            $('#interestsResult').selectpicker('refresh');
            $('#load').hide();
        },
        'json',
    );
}
$(document).ready(function () {
    $('#interestsText').on('keyup', _.throttle(fetchSuggestions, 500));
});

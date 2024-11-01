(function ($) {
    $(document).ready(function ($) {
        $('input[data-input-type="range"]').each(function () {
            const $this = $(this);
            let settingsID = $this.attr('data-customize-setting-link');
            wp.customize(settingsID, function(control) {
                control.bind(function (to) {
                    $('[data-customize-setting-link="'+settingsID+'"]').val(to).change();
                })
            });

        })
        $('input[data-input-type]').on('input change', function () {
            var val = $(this).val();
            $(this).prev('.cs-range-value').html(val);
            $(this).val(val);
        });
        // Gestionnaire d'événements pour le bouton de réinitialisation
        $('.reset-default').on('click', function(event) {
            event.preventDefault(); // Empêche le comportement par défaut du bouton

            var control = $(this).closest('.customize-control');
            var settingId = control.find('input[type="range"]').data('customize-setting-link');

            // Réinitialisez la valeur du réglage à sa valeur par défaut
            wp.customize(settingId, function(setting) {
                setting.set(default_params[settingId]);
                control.find('.cs-range-value').text(default_params[settingId]);
            });
        });
    })
})(jQuery);
<script language="javascript" src="~url::baseurl~/web/galleria/galleria-1.2.9.min.js"></script>
<script>
    $(document).ready(function () {
        Galleria.loadTheme('~url::baseurl~/web/galleria/themes/classic/galleria.classic.min.js');
        Galleria.run('#galleria', {
            autoplay: 7000,
            lightbox: true
        });
    });
</script>

~gallery~

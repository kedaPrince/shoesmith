<?PHP
if ($this->quickManage == true) {
?>
    <script>
        if (typeof $ === 'function') {
            $('.data-table').on('click', 'a.vieww-row', function(e) {
                e.stopPropagation();
                e.preventDefault();
                var id = $(this).closest('tr').attr('data-row-id');

                setTimeout(() => {
                    $(this).tooltip('hide');
                }, 100);

                ajax_get('ajax_view/' + id, '', function(d) {
                    if (d) {
                        open_qm(d);
                    }
                });

                setTimeout(() => {
                    $(this).tooltip('hide');
                }, 100);
            });
        }
    </script>
<?php
}

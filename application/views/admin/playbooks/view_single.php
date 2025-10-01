<?php
defined('BASEPATH') || exit('No direct script access allowed');
?>
<style>
    h2.section-heading {
        text-transform: uppercase;
    }
</style>
<div id="main-content">
    <div class="container-fluid">
        <h1 class="block-header"><?= $playbook->name ?></h1>
        <section>
            <?php
            if ($access_allowed) {

                // Generate the index if set
                if ((int)$playbook->generate_index_page > 0) {
            ?>
                    <div class="row-holder page-index" style="margin-top:2rem;">
                        <h2 class="section-heading">INDEX</h2>
                        <p>NOTE : All the titles within the Index below are linked to that particular section within the document. You can just click on the title should you need to quickly view that information.</p>
                        <ul>
                            <?php
                            foreach ($playbook_sections as $row) {
                            ?>
                                <li><a rel="<?= $row->playbook_slug ?>"><?= $row->name ?></a></li>
                            <?php
                            }
                            ?>
                        </ul>
                    </div>
                    <p>&nbsp;</p>
                <?php
                }

                // Render page sections
                foreach ($playbook_sections as $index => $row) {
                    if ($index > 0) {
                        echo '<p>&nbsp;</p>', "\r\n";
                    }
                ?>
                    <h2 class="section-heading"><?= $row->name; ?></h2>
                    <?= str_replace(['</p>', '</ol>'], ['</p>' . "\r\n", '</ol>' . "\r\n"], $row->content); ?>
                <?php
                }
            } else {
                ?>
                <div>
                    <h2>You do not have permission to view this document</h2>
                </div>
            <?php
            }
            ?>
        </section>
    </div>
</div>
<?php
defined('BASEPATH') || exit('No direct script access allowed');
?>
<a class="close-quick-manage"><i class="fa fa-times"></i></a>
<div class="quick-manage-form-container qmfc">
    <div class="form-field-container-view">
        <h1 class="block-header"><?= $row->name ?></h1>
        <section>
            <?php
            if ($access_allowed) {

                // Render page sections
            ?>
                <!-- <h2><?= $row->name; ?></h2> -->
                <?= str_replace(['</p>', '</ol>'], ['</p>' . "\r\n", '</ol>' . "\r\n"], $row->content); ?>
            <?php
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
<style>
    .form-field-container-view {
        box-sizing: border-box;
        height: calc(100% + 50px);
        overflow-y: auto;
        padding: 0 2rem 0.5rem 2rem;
    }
</style>
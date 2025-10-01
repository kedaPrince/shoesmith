<?php
defined('BASEPATH') || exit('No direct script access allowed');
?>
<a class="close-quick-manage"><i class="fa fa-times"></i></a>
<div class="quick-manage-form-container qmfc">
    <div class="form-field-container-view">
        <h1 class="block-header"><?= $playbook->name ?></h1>
        <?php
        if ($access_allowed) {

            // Generate the index if set
            if ((int)$playbook->generate_index_page > 0) {
        ?>
                <section class="view-section">
                    <div class="row-holder page-index" style="margin-top:2rem;">
                        <h2 class="section-heading">INDEX</h2>
                        <p>NOTE : All the titles within the Index below are linked to that particular section within the document. You can just click on the title should you need to quickly view that information.</p>
                        <ul>
                            <?php
                            foreach ($playbook_sections as $playbook_section) {
                            ?>
                                <li><a rel="<?= $playbook_section->playbook_slug ?>"><?= $playbook_section->name ?></a></li>
                            <?php
                            }
                            ?>
                        </ul>
                    </div>
                </section>
            <?php
            }

            // Render introduction
            foreach ($playbook_sections as $index => $playbook_section) {
                if ($index > 0) {
                    break;
                }
            ?>
                <section class="view-section introduction">
                    <h2 class="section-heading"><?= $playbook_section->name; ?></h2>
                    <?= str_replace(['</p>', '</ol>'], ['</p>' . "\r\n", '</ol>' . "\r\n"], $playbook_section->content); ?>
                </section>
            <?php
            }

            if (!empty($document_index) && (int)$playbook->generate_references > 0) {
            ?>
                <section class="view-section">
                    <h2 class="section-heading">References</h2>
                    <ul>
                        <?php
                        foreach ($document_index as $url => $url_title) {
                        ?>
                            <li><a target="_blank" href="<?= $url ?>" class="uppercase"><?= $url_title ?></a></li>
                        <?php
                        }
                        ?>
                    </ul>
                </section>
            <?php
            }

            // Render page sections without the introduction section
            foreach ($playbook_sections as $index => $playbook_section) {
                if ($index === 0) {
                    continue;
                }
            ?>
                <section class="view-section">
                    <h2 class="section-heading"><?= $playbook_section->name; ?></h2>
                    <?= str_replace(['</p>', '</ol>'], ['</p>' . "\r\n", '</ol>' . "\r\n"], $playbook_section->content); ?>
                </section>
            <?php
            }
        } else {
            ?>
            <section class="view-section">
                <div>
                    <h2>You do not have permission to view this document</h2>
                </div>
            </section>
        <?php
        }
        ?>
    </div>
</div>
<style>
    h2.section-heading {
        text-transform: uppercase;
    }

    section.view-section {
        margin-bottom: 2rem;
    }

    section.view-section:last-of-type {
        margin-bottom: 0;
    }

    .form-field-container-view {
        box-sizing: border-box;
        height: calc(100% + 50px);
        overflow-y: auto;
        padding: 0 2rem 0.5rem 2rem;
    }
</style>
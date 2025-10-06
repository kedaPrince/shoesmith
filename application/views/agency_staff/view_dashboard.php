<section class="dashboard-container">
    <div>
        <h1>Welcome Agency</h1>
        <h2>What would you like to see?</h2>
        <ul>
            <?php
            foreach ($this->siteMap as $group) { 
                if (!$group->show) {
                    continue;
                }
                
                // Check if the group has sub-items
                if (isset($group->items) && is_array($group->items)) {
                    foreach ($group->items as $subItem) {
                        if (!$subItem->show || (isset($subItem->view) && $subItem->view == 'add')) {
                            continue;
                        }
                        
                        echo '
                            <li>
                                <a href="'.$subItem->url.'" title="'.$subItem->label.'">
                                    <i class="fa '.$subItem->icon.'" ></i>
                                    <span>'.$subItem->label.'</span>
                                </a>
                            </li>
                        ';

                        // Check for nested sub-items
                        if (isset($subItem->items) && is_array($subItem->items)) {
                            foreach ($subItem->items as $nestedSubItem) {
                                if (!$nestedSubItem->show || (isset($nestedSubItem->view) && $nestedSubItem->view == 'add')) {
                                    continue;
                                }
                                
                                echo '
                                    <li class="nested-sub-item">
                                        <a href="'.$nestedSubItem->url.'" title="'.$nestedSubItem->label.'">
                                            <i class="fa '.$nestedSubItem->icon.'" ></i>
                                            <span>'.$nestedSubItem->label.'</span>
                                        </a>
                                    </li>
                                ';
                            }
                        }
                    }
                } else {
                    echo '
                        <li>
                            <a href="'.$group->url.'" title="'.$group->label.'">
                                <i class="fa '.$group->icon.'" ></i>
                                <span>'.$group->label.'</span>
                            </a>
                        </li>
                    ';
                }
            }
            ?>
        </ul>
    </div>
</section>
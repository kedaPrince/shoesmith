<section class="dashboard-container">
    <div>
        <h1><?php echo isset($welcome_message) ? $welcome_message : 'Welcome Agency Staff Member'; ?></h1>
        
        <?php if (isset($show_debug) && $show_debug && isset($debug_info)): ?>
        <div class="debug-info" style="background: #f8f9fa; padding: 15px; margin: 15px 0; border-radius: 5px; border: 1px solid #dee2e6;">
            <h4 style="margin-top: 0;">Access Information</h4>
            <p><strong>Access Groups:</strong> 
                <?php echo !empty($debug_info['group_names']) ? implode(', ', $debug_info['group_names']) : 'None'; ?>
            </p>
            <p><strong>Group IDs:</strong> 
                <?php echo !empty($debug_info['group_ids']) ? implode(', ', $debug_info['group_ids']) : 'None'; ?>
            </p>
            <p><strong>Has Full Access:</strong> <?php echo $debug_info['has_full_access'] ? 'Yes' : 'No'; ?></p>
            <a href="<?php echo site_url('agency_staff/dashboard/test_access'); ?>" target="_blank" 
               style="display: inline-block; background: #007bff; color: white; padding: 5px 10px; border-radius: 3px; text-decoration: none;">
                Test Access System
            </a>
        </div>
        <?php endif; ?>
        
        <h2>Available Options</h2>
        
        <?php if (empty($this->siteMap)): ?>
            <div class="alert alert-info">
                <p>No menu items available for your access level.</p>
                <p>Please contact your administrator to request access to additional features.</p>
            </div>
        <?php else: ?>
            <div class="dashboard-menu">
                <ul style="list-style: none; padding: 0; display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">
                    <?php
                    foreach ($this->siteMap as $group) { 
                        if (!$group->show) continue;
                        
                        // Handle groups with sub-items
                        if (isset($group->items) && is_array($group->items)) {
                            foreach ($group->items as $subItem) {
                                if (!$subItem->show || (isset($subItem->view) && $subItem->view == 'add')) continue;
                                
                                echo '
                                    <li style="background: white; border: 1px solid #dee2e6; border-radius: 5px; padding: 15px;">
                                        <a href="'.$subItem->url.'" title="'.$subItem->label.'" style="text-decoration: none; color: #333; display: block;">
                                            <div style="font-size: 24px; color: #007bff; margin-bottom: 10px;">
                                                <i class="fa '.$subItem->icon.'"></i>
                                            </div>
                                            <div style="font-weight: bold;">'.$subItem->label.'</div>
                                        </a>
                                    </li>
                                ';
                            }
                        } else {
                            // Main group item
                            echo '
                                <li style="background: white; border: 1px solid #dee2e6; border-radius: 5px; padding: 15px;">
                                    <a href="'.$group->url.'" title="'.$group->label.'" style="text-decoration: none; color: #333; display: block;">
                                        <div style="font-size: 24px; color: #007bff; margin-bottom: 10px;">
                                            <i class="fa '.$group->icon.'"></i>
                                        </div>
                                        <div style="font-weight: bold;">'.$group->label.'</div>
                                    </a>
                                </li>
                            ';
                        }
                    }
                    ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<?php
$submodules = $this->session->submodules;
$submodule  = false;
if (!empty($submodules[$this->pageName])) {
    $submodule = $submodules[$this->pageName];
}

/* Amount of results */
//echo "<tr class='hidden'><td id='amount'>" . $amount . "</td></tr>";

$count = 0;
foreach ($query->result() as $row) {
    $count++;

    $class = $row->enabled ? ' row-enabled ' : ' row-disabled ';
    //Add any extra classes to the tr
    if (!empty($this->listRowClass) && is_callable($this->listRowClass)) {
        $class .= " " . call_user_func($this->listRowClass, $row, $batch) . " ";
    } elseif (!empty($this->listRowClass)) {
        $class .= " " . $this->listRowClass . " ";
    }

    $class .= $count % 2 ? ' odd ' : ' even ';

    $identifier = !empty($row->name) ? $row->name : lang('this_row');
	$identifier = !empty($row->{$this->identifierField}) ? $row->{$this->identifierField} : lang('this_row');
    $identifier = !empty($this->identifier) ? $this->identifier : $identifier;

    echo '<tr class="data-table-row '.$class.'" data-row-id="'.$row->id.'" data-row-batch="'.$batch.'" data-identifier="'.$identifier.'">';
    $section     = uri_segment(4, '');
    $list         = !empty($this->sections) && isset($this->listFields[$section]) ? $this->listFields[$section] : $this->listFields;
    $actions     = !empty($this->listActions) && isset($this->listActions[$section]) ? $this->listActions[$section] : $this->listActions;

    //If list items can expand, get html for it
    $expandedHTML = "";
    if (!empty($this->listExpanded) && is_callable($this->listExpanded)) {
        $expandedHTML = call_user_func($this->listExpanded, $row, $batch);
    }

    //If list items can expand, then add expand/collapse button
    if (!empty($this->listExpanded) && ! empty($expandedHTML)) {
		echo '<td class="list-expander"><i class="fa fa-plus"></i></td>';
    } else if ( ! empty($this->listExpanded)) {
        echo '<td class="list-expander"></td>';
    }

    foreach ($list as $field => $options) {
        //Skip field if specified in submodule options
        if ($submodule && in_array($field, $submodule->hideFields)) {
            continue;
        }

		//Skip if field is set to hidden
		if (isset($options['show']) && !$options['show']) {
			continue;
		}

		$value = '';
        $rowClass = ($field == 'position') ? "no-click" : "";
        $colClass = !empty($options['class']) ? $options['class'] : "";
        $fieldType     = !empty($options['type']) ? $options['type'] : 'field';
        if (!empty($options['function']) && is_callable($options['function'])) {
        //Runs the function specified to format/change the value before showing it
            $value = call_user_func($options['function'], $row->{$field}, $row);
        }
		elseif ($fieldType == 'tags') {
			if (isset($row->{$field})) {
				$tags 		= explode(',', $row->{$field});
				$tagClass 	= !empty($options['tagClass']) ? $options['tagClass'] : 'badge-secondary';

				foreach ($tags as $tag) {
					if (empty($tag)) {
						continue;
					}
					$tagData 	= explode('|', $tag);
					$tagLink 	= !empty($options['tagLink']) ? 'href="'.str_replace('{id}', $tagData[0], $options['tagLink']).'"' : '';

					if (isset($tagData[1])) {
						// Add disabled class if available, selectFieldExtra needs to be using enabled
						if (count($tagData) - 1 > 1 && isset($tagData[count($tagData) - 1]) && is_numeric($tagData[count($tagData) - 1])) {
							$tagClass .= (int)$tagData[count($tagData) - 1] > 0 ? '' : ' badge-disabled';
						}
						$value .= '<a '.$tagLink.' class="badge '.$tagClass.'" onclick="event.stopPropagation();">'.$tagData[1].'</a>';
					}
				}
			}
		}
        else {
            $value = $row->{$field};
        }

        // Limit the text length and add ellipsis if the option is set
        if (!empty($options['maxLength']) && strlen($value) > $options['maxLength']) {
            $value = substr(strip_tags($value), 0, $options['maxLength']) . '...';
        }

        $tooltipClass = (isset($options['expandAsTooltip']) && $options['expandAsTooltip']) ? "has-tooltip" : "";
        $tooltipAttr = (isset($options['expandAsTooltip']) && $options['expandAsTooltip']) ? ' data-toggle="listing-tt" data-placement="top" title="' . strip_tags($row->{$field}) . '"' : "";

        echo '<td class="' . trim($rowClass . ' ' . $colClass . ' ' . $tooltipClass) . '" data-field-value="' . $field . '"' . $tooltipAttr . '>' . $value . '</td>';
    }
    //List Actions
    if (!empty($actions)) {
        echo '
                <td class="general-actions">
        ';
        $actionButtons = array();
        foreach ($actions as $action) {
            //Check if action require special permissions
            if (empty($action['restrict']) || ( ! empty($action['restrict']) && Permissions::has($action['restrict']))) {
                //Check if function is set
                if ( ! empty($action['function']) && is_callable($action['function'])) {
                    //Runs the function specified to format/change the value before showing it
                    $actionValue = call_user_func($action['function'], $action['label'], $row);
                } else {
                    $actionValue = $action['label'];
                }
                $class = ! empty($action['class']) ? $action['class'] : '';
                $icon = ! empty($action['icon']) ? $action['icon'] : 'view_agenda';
                $url = preg_replace_callback('/\{([a-z_]+)\}/', (function ($matches) use ($row) {
                    $tag = $matches[1];
                    if (isset($row->{$tag})) {
                        return $row->{$tag};
                    }
                }), $action['url']);
                $target = ! empty($action['target']) ? $action['target'] : "_self";
                if ($actionValue) {
                    $actionButtons[] = array(
                        'url'         => $url,
                        'target'     => $target,
                        'class'        => $class,
                        'text'        => $actionValue,
                        'icon'        => $icon
                    );
                }
            }
        }

        //Loop out buttons, but if there is more than 4 buttons then show action listing button and show the rest of the action items in there
        $ac = 0;
        foreach ($actionButtons as $aButton) {
            $ac++;

            if($ac < 4 || (count($actionButtons) == 4 && $ac == 4)) {
                echo '<a href="'.$aButton['url'].'" target="'.$aButton['target'].'" class="btn btn-sm btn-outline-secondary '.$aButton['class'].'" data-toggle="listing-tt" data-placement="top" title="'.$aButton['text'].'"><i class="fa '.$aButton['icon'].'"></i></a>';
            }
            else {

                if ($ac == 4) {
                    //Open action listing
                    echo '<a class="btn btn-grey open_action_list"><i class="fa fa-ellipsis-v"></i></a>
                        <ul class="action-list-menu">
                    ';
                }

                echo '
                            <li><a href="'.$aButton['url'].'" target="'.$aButton['target'].'" class="'.$aButton['class'].'"><i class="fa '.$aButton['icon'].'"></i>'.$aButton['text'].'</a></li>
                ';

                if ($ac == count($actionButtons)) {

                    echo '
                        </ul>
                    ';
                }
    
            }



            
        }

        echo '
                </td>
            ';
    } else {
        echo '<td></td>';
    }
    echo '</tr>';

    $expandedClass = !empty($this->expandedRowClass) ? $this->expandedRowClass : '';
    if (!empty($expandedHTML)) {
        echo '
        <tr class="expanded-list-item ' . $expandedClass . '">
            <td colspan="' . (count($list) + 2) . '">
                <div>
                    ' . $expandedHTML . '
                </div>
            </td>
         </tr>';
    }
}
<?php
    $table_col = [
        'name' => [
            'title' => _('File Name'),
            'help'  => _('Name of file, relative to Asterisk logpath. Use absolute path for a different location'),
            'class' => 'col-md-2',
            'data-formatter' => 'logfiles_cell_id'
        ],
        'debug' => [
            'title' => _('Debug'),
            'help'  => _('Messages used for debuging. '
                        . 'Do not report these as error\'s unless you have a specific issue that you are attempting to debug. '
                        . 'Also note that Debug messages are also very verbose and can and do fill up logfiles (and disk storage) quickly.'),
            'class' => 'text-center',
            'data-formatter' => 'logfiles_cell_dropdown'
        ],
        'dtmf' => [
            'title' => _('DTMF'),
            'help'  => _('Keypresses as understood by asterisk. Usefull for debuging IVR and VM issues.'),
            'class' => 'text-center',
            'data-formatter' => 'logfiles_cell_dropdown'
        ],
        'error' => [
            'title' => _('Error'),
            'help'  => _('Critical errors and issues'),
            'class' => 'text-center',
            'data-formatter' => 'logfiles_cell_dropdown'
        ],
        'fax' => [
            'title' => _('Fax'),
            'help'  => _('Transmition and receiving of faxes'),
            'class' => 'text-center',
            'data-formatter' => 'logfiles_cell_dropdown'
        ],
        'notice' => [
            'title' => _('Notice'),
            'help'  => _('Messages of specific actions, such as a phone registration or call completion'),
            'class' => 'text-center',
            'data-formatter' => 'logfiles_cell_dropdown'
        ],
        'verbose' => [
            'title' => _('Verbose'),
            'help'  => _('Step-by-step messages of every step of a call flow. '
                        . 'Always enable and review if calls dont flow as expected'),
            'class' => 'text-center',
            'data-formatter' => 'logfiles_cell_dropdown'
        ],
        'warning' => [
            'title' => _('Warning'),
            'help'  => _('Possible issues with dialplan syntaxt or call flow, but not critical.'),
            'class' => 'text-center',
            'data-formatter' => 'logfiles_cell_dropdown'
        ],

        'security' => [
            'title' => _('Security'),
            'help'  => "Security messages.",
            'class' => 'text-center',
            'data-formatter' => 'logfiles_cell_dropdown'
        ],
        'acction' => [
            'title' => _('Actions'),
            'class' => 'col-md-2 text-center',
            'data-formatter' => 'logfiles_cell_acction',
            'data-field' => 'name'
        ]
    ];
?>

<div id="toolbar-grid">
    <a href="#" class="btn btn-default" id="logfiles_add_new_line"><i class="fa fa-plus"></i> <?php echo _("New Log")?></a>
</div>
<table id="logfile_entries" class="table table-condensed table-striped"
	data-cache="false"
    data-toolbar="#toolbar-grid"
	data-show-columns="false"
	data-show-toggle="false"
	data-pagination="true"
	data-search="true"
	data-show-refresh="true"
	data-escape="true" 
	data-url="ajax.php?module=logfiles&amp;command=logfiles_get_all"
	data-unique-id="name"
	data-toggle="table">
	<thead>
		<tr>
            <?php
                foreach ($table_col as $key => $val)
                {
                    $for_code = 'data-field='.( empty($val['data-field']) ? $key : $val['data-field'] );
                    
                    $for_code .= ( empty($val['class'])          ? '' : sprintf(' class="%s"', $val['class']) );
                    $for_code .= ( empty($val['help'])           ? '' : sprintf(' data-title-tooltip="%s"', $val['help']) );
                    $for_code .= ( empty($val['data-formatter']) ? '' : sprintf(' data-formatter="%s"', $val['data-formatter']) );

                    echo sprintf('<th scope="col" %s>%s</th>', $for_code, $val['title']);
                }
            ?>
		</tr>
	</thead>
</table>

<?php
/*
 * Created on 12/08/2008 by rroca
 *
 * functions for managing supplier node types
 */

function budgets_supplier_help($path, $arg) {
  if ($path == 'admin/help#budgets_supplier') {
    $txt = 'A supplier is either an individual or a company who provides ' .
        'services or materials to other network participants to help in ' .
        'building infrastructures';
    $replace = array();
    return '<p>'.t($txt,$replace).'</p>';
  }
}

function budgets_supplier_form(&$node,&$param) {
  $type = node_get_types('type',$node);

  if (!empty($param['values'])) {
     $node = (object)$param['values'];
  }

  guifi_log(GUIFILOG_TRACE, 'function budgets_supplier_form(READ)', $node->role);

  if (($type->has_title)) {
    $form['title'] = array(
      '#type' => 'textfield',
      '#title' => check_plain($type->title_label),
      '#required' => TRUE,
      '#default_value' => $node->title,
    );
  }

  /*
   * Professional or Volunteer?
   */
  $form['role'] = array(
    '#type'        => 'radios',
    '#title'       => t('Role'),
    '#options'     => array('volunteer'=>t('Volunteer'),'professional'=>t('Professional')),
    '#default_value'=>$node->role,
    '#description' => t(
       'Use professional for Service Level commitments and economic professional activities, volunteer when is not.<br>' .
       'Note: You can rebuild the form to select available capabilities by pressing "Preview" button.'),
    '#required'    => TRUE,
  );

  /*
   * Address
   */
  $form['address'] = array(
    '#type'        => 'fieldset',
    '#title'       => t('Address'),
    '#collapsible' => TRUE,
    '#collapsed'   => TRUE,
  );
  if (empty($node->address1) or empty($node->postal_code) or empty($node->city) or
      empty($node->country)
  )
    $form['address']['#collapsed']=FALSE;
  $form['address']['address1'] = array(
    '#type'             => 'textfield',
    '#size'             => 256,
    '#maxlength'        => 256,
    '#title'            => t('Postal Address'),
    '#required'         => TRUE,
    '#default_value'    => $node->address1,
  );
  $form['address']['address2'] = array(
    '#type'             => 'textfield',
    '#size'             => 256,
    '#maxlength'        => 256,
    '#required'         => FALSE,
    '#default_value'    => $node->address2,
  );
  $form['address']['postal_code'] = array(
    '#type'             => 'textfield',
    '#title'            => t('Postal Code'),
    '#description'      => t('Postal or zip code'),
    '#size'             => 10,
    '#maxlength'        => 20,
    '#required'         => TRUE,
    '#default_value'    => $node->postal_code,
  );
  $form['address']['city'] = array(
    '#type'             => 'textfield',
    '#title'            => t('City'),
    '#size'             => 25,
    '#maxlength'        => 128,
    '#required'         => TRUE,
    '#default_value'    => $node->city,
  );
  $form['address']['region'] = array(
    '#type'             => 'textfield',
    '#title'            => t('Region or province'),
    '#size'             => 25,
    '#maxlength'        => 128,
    '#required'         => FALSE,
    '#default_value'    => $node->region,
  );
  $form['address']['country'] = array(
    '#type'             => 'textfield',
    '#title'            => t('Country'),
    '#size'             => 25,
    '#maxlength'        => 128,
    '#required'         => TRUE,
    '#default_value'    => $node->country,
  );

  /*
   * Zones
   */
  $zone_ids = array();

  $form['zones'] = array(
    '#type'        => 'fieldset',
    '#title'       => t('Zones'),
    '#description' => t('Zones where the offerings are available, includes the selected node and childs belonging to the selected zone.<br>'.
      'Use "Preview" button if all nodes are filled, and you need more rows to fill.'),
    '#collapsible' => TRUE,
    '#collapsed'   => ($node->zones[0]!='') ? TRUE : FALSE,
    '#attributes'  => array('class'=>'zones'),
    '#tree'        => TRUE,
  );
  $zone_id=0;
  $nzones = count($node->zones);
  guifi_log(GUIFILOG_TRACE, 'function budgets_supplier_form(zones)', $node->zones);
  do {
    $form['zones'][$zone_id] = array (
      '#type' => 'textfield',
      '#size' => 80,
      '#default_value'=> ($node->zones[$zone_id]!='') ?
         $node->zones[$zone_id].'-'.guifi_get_zone_name($node->zones[$zone_id]) : NULL,
      '#maxsize'=> 256,
      '#autocomplete_path' => 'budgets/js/select-zone',
    );
    $zone_id++;
  } while ($zone_id < ($nzones + 3));

  if (($type->has_body)) {
    $form['body_field'] = node_body_field(
      $node,
      $type->body_label,
      $type->min_word_count
    );
  }

  /*
   * Tax & fiscal information
   */
  $zone_ids = array();

  $form['tax'] = array(
    '#type'        => 'fieldset',
    '#title'       => t('Tax & Fiscal information'),
    '#description' => t('Zones where the offerings are available, includes the selected node and childs belonging to the selected zone.<br>'.
      'Use "Preview" button if all nodes are filled, and you need more rows to fill.'),
    '#collapsible' => TRUE,
    '#collapsed'   => ($node->tax_code!='') ? TRUE : FALSE,
    '#attributes'  => array('class'=>'tax'),
  );
  $form['tax']['tax_code'] = array(
    '#type'             => 'textfield',
    '#size'             => 25,
    '#maxlength'        => 50,
    '#title'            => t('Tax Code / Fiscal Number'),
    '#required'         => TRUE,
    '#default_value'    => $node->tax_code,
    '#description'      =>  t('Whatever is the legal id in your country for tax and fiscal purposes.')
  );
  $form['tax']['default_tax_rate'] = array(
    '#type'             => 'textfield',
    '#size'             => 25,
    '#maxlength'        => 50,
    '#title'            => t('Default tax rate'),
    '#required'         => TRUE,
    '#default_value'    => $node->default_tax_rate,
    '#description'      =>  t('When loading quotes and items into proposals.<br> "0" means that the prices are given with the tax included.')
  );

  /*
   * Certifications
   */
  if (!empty($node->role))
  $form['certs'] = array(
    '#type'        => 'fieldset',
    '#title'       => t('Enabling Certifications'),
    '#description' => t('Certificates held by the supplier for empowering activities.<br>'.
      "Leave blank if unknown/don't have or specify issue date in DD/MM/YYYY format. Note that the provider is required to send a copy of some of those certifications to the Foundation, and be available at any time upon request."),
    '#collapsible' => TRUE,
    '#collapsed'   => (($node->tp_certs) or ($node->guifi_certs) or ($node->other_certs)) ?
       TRUE : FALSE,
    '#tree'        => TRUE,
  );
  $options_tp_certs=guifi_types('tp_certs');
  $count = 1; $totalv = count($options_tp_certs);
  if ($node->role=='professional')
    foreach ($options_tp_certs as $key=>$cert) {
  	  $prefix = ''; $suffix = '';
  	  if ($count == 1)
  	    $prefix = '<div class=certs><table><tr><th>'.
          t('date').'</th><th>'.
          t('Professional certificate').'</th></tr>';
    	if ($count == $totalv)
          $suffix = '</table></div>';
  	  $form['certs']['tp_certs'][$key] = array(
        '#type' => 'textfield',
        '#size' => 10,
        '#default_value' => $node->certs['tp_certs'][$key],
        '#attributes'=>array('class'=>"cert-field"),
        '#prefix' => $prefix.'<tr><td>',
        '#suffix' => '</td><td>'.$cert.'</td>'.$suffix,
      );
      $count++;
    }
  $options_guifi_certs=guifi_types('guifi_certs');
  $count = 1; $totalv = count($options_guifi_certs);
  if (!empty($node->role))
    foreach ($options_guifi_certs as $key=>$cert) {
    	$prefix = ''; $suffix = '';
  	  if ($count == 1)
  	    $prefix = '<div class=certs><table><tr><th>'.
          t('date').'</th><th>'.
          t('guifi.net certificate').'</th></tr>';
    	if ($count == $totalv)
  	  $suffix = '</table></div>';
    	$form['certs']['guifi_certs'][$key] = array(
        '#type' => 'textfield',
        //'#title' => t($cert),
        '#size' => 10,
        '#default_value' => $node->certs['guifi_certs'][$key],
        '#attributes'=>array('class'=>"cert-field"),
        '#prefix' => $prefix.'<tr><td>',
        '#suffix' => '</td><td>'.$cert.'</td>'.$suffix,
      );
      $count++;
    }
  if (!empty($node->role))
  $form['certs']['other_certs'] = array(
    '#type'             => 'textfield',
    '#title'            => t('Other certificates'),
    '#description'      => t('Other certifications that might be on the interest for the activity, i.e. Quality,ISO Certs, ...<br>'.
                             'List them sepparated by commas.'),
    '#size'             => 256,
    '#maxlength'        => 1024,
    '#required'         => FALSE,
    '#tree'             => FALSE,
    '#default_value'    => $node->other_certs,
  );

  /*
   * Capabilities
   */
  $skills_list=guifi_types('skills');
  if (!empty($node->role))
  foreach ($skills_list as $k=>$value)
     if (guifi_type_relation('skills',$k,$node->role))
       $skill_opts[$k] = $value;
  if (!empty($node->role))
  $form['caps'] = array(
    '#type'        => 'fieldset',
    '#title'       => t('Capabilities, services & offerings'),
    '#description' => t('Grid for selecting capabilities, offerings & services built for %role. Press "Preview" if you switched the role to rebuild this grid.',
      array('%role'=>$node->role)),
    '#collapsible' => TRUE,
    '#collapsed'   => (($node->caps_services) or ($node->caps_network) or ($node->caps_services) or ($node->other_caps)) ?
      TRUE : FALSE,
    '#tree'        => TRUE,
  );

  $skills=t('capability & skills');
  $opt_caps=guifi_types('caps_services');
  $count = 1; $totalv = count($opt_caps);
  if (!empty($node->role))
  foreach ($opt_caps as $key=>$cap) {
  	$prefix = ''; $suffix = '';
  	if ($count == 1)
  	  $prefix = '<div class=certs><table><tr><th>'.
        $skills.'</th><th>'.
        t('services & content providers').'</th></tr>';
  	if ($count == $totalv)
	  $suffix = '</table></div>';
  	$form['caps']['caps_services'][$key] = array(
      '#type' => 'select',
      '#options'=>$skill_opts,
      '#disabled'=>(guifi_type_relation('caps_services',$key,$node->role))?
         false:true,
      '#default_value' => $node->caps['caps_services'][$key],
      '#attributes'=>array('class'=>"cert-field"),
      '#prefix' => $prefix.'<tr><td>',
      '#suffix' => (guifi_type_relation('caps_services',$key,$node->role)) ?
                    '</td><td>'.$cap.'</td>'.$suffix :
                    '</td><td><strike>'.$cap.'</strike></td>'.$suffix,
    );
    $count++;
  }

  $opt_caps=guifi_types('caps_network');
  $count = 1; $totalv = count($opt_caps);
  if (!empty($node->role))
  foreach ($opt_caps as $key=>$cap) {
  	$prefix = ''; $suffix = '';
  	if ($count == 1)
  	  $prefix = '<div class=certs><table><tr><th>'.
        $skills.'</th><th>'.
        t('network dev. & mgmt.').'</th></tr>';
  	if ($count == $totalv)
	  $suffix = '</table></div>';
  	$form['caps']['caps_network'][$key] = array(
      '#type' => 'select',
      '#options'=>$skill_opts,
      '#disabled'=>(guifi_type_relation('caps_network',$key,$node->role))?
         false:true,
      '#default_value' => $node->caps['caps_network'][$key],
      '#attributes'=>array('class'=>"cert-field"),
      '#prefix' => $prefix.'<tr><td>',
      '#suffix' => (guifi_type_relation('caps_network',$key,$node->role)) ?
                    '</td><td>'.$cap.'</td>'.$suffix :
                    '</td><td><strike>'.$cap.'</strike></td>'.$suffix,
    );
    $count++;
  }

  $opt_caps=guifi_types('caps_project');
  $count = 1; $totalv = count($opt_caps);
  if (!empty($node->role))
  foreach ($opt_caps as $key=>$cap) {
  	$prefix = ''; $suffix = '';
  	if ($count == 1)
  	  $prefix = '<div class=certs><table><tr><th>'.
        $skills.'</th><th>'.
        t('project development').'</th></tr>';
  	if ($count == $totalv)
	  $suffix = '</table></div>';
  	$form['caps']['caps_project'][$key] = array(
      '#type' => 'select',
      '#options'=>$skill_opts,
      '#disabled'=>(guifi_type_relation('caps_project',$key,$node->role))?
         false:true,
      '#default_value' => $node->caps['caps_project'][$key],
      '#prefix' => $prefix.'<tr><td>',
      '#suffix' => (guifi_type_relation('caps_project',$key,$node->role)) ?
                    '</td><td>'.$cap.'</td>'.$suffix :
                    '</td><td><strike>'.$cap.'</strike></td>'.$suffix,
    );
    $count++;
  }
  if (!empty($node->role))
  $form['caps']['other_caps'] = array(
    '#type'             => 'textfield',
    '#title'            => t('Other capabilities'),
    '#description'      => t('Other capabilities & oferings of this supplier that might be on the interest for networking infrastructure deployment and management.<br>'.
                             'List them sepparated by commas.'),
    '#size'             => 256,
    '#maxlength'        => 1024,
    '#required'         => FALSE,
    '#tree'             => FALSE,
    '#default_value'    => $node->other_caps,
  );

  /*
   * Ratings
   */
  $form['ratings'] = array(
    '#type'        => 'fieldset',
    '#title'       => t('Ratings'),
    '#description' => t('Self evaluation and objective rating'),
    '#collapsible' => TRUE,
    '#collapsed'   => (($node->self_rating!='~~') or ($node->official_rating!='~~')) ?
      TRUE : FALSE,
  );
  $form['ratings']['self'] = array(
    '#type'        => 'fieldset',
    '#title'       => t('Self Ratings').' '.$node->self_rating,
    '#description' => t('Self evaluation'),
    '#collapsible' => TRUE,
    '#collapsed'   => FALSE,
  );
  $form['ratings']['self']['sr_commitment'] = array(
    '#type'        => 'select',
    '#title'       => t('Commitment'),
    '#description' => t('Commitment to the Commons and the Community'),
    '#default_value'=>($node->sr_commitment)?($node->sr_commitment):'~',
    '#options'     => guifi_types('commitment_rate'),
  );
  $form['ratings']['self']['sr_experience'] = array(
    '#type'        => 'select',
    '#title'       => t('Experience'),
    '#default_value'=>($node->sr_experience)?($node->sr_experience):'~',
    '#description' => t('Proven experience on executed projects'),
    '#options'     => guifi_types('experience_rate'),
  );

  $form['ratings']['official'] = array(
    '#type'        => 'fieldset',
    '#title'       => t('Official Ratings').' '.$node->official_rating,
    '#description' => t('Objective evaluation'),
    '#collapsible' => TRUE,
    '#collapsed'   => FALSE,
 //   '#access'      => user_access('official rating'),
  );
  $form['ratings']['official']['or_commitment'] = array(
    '#type'        => 'select',
    '#title'       => t('Commitment'),
    '#description' => t('Commitment to the Commons and the Community'),
    '#default_value'=>($node->or_commitment)?($node->or_commitment):'~',
    '#options'     => guifi_types('commitment_rate'),
    '#access'      => user_access('official rating'),
  );
  $form['ratings']['official']['or_experience'] = array(
    '#type'        => 'select',
    '#title'       => t('Experience'),
    '#default_value'=>($node->or_experience)?($node->or_experience):'~',
    '#options'     => guifi_types('experience_rate'),
    '#access'      => user_access('official rating'),
  );
  $form['ratings']['official']['or_trend'] = array(
    '#type'        => 'select',
    '#title'       => t('Trend'),
    '#description' => t('Revision to'),
    '#default_value'=>($node->or_trend)?($node->or_trend):' ',
    '#options'     => array('+'=>'+',' '=>t('Stable'),'-'=>'-'),
    '#access'      => user_access('official rating'),
  );

  /*
   * Accounting URLs
   */
  $k=0; $total=count($node->accounting_urls);
  $form['accounting_urls'] = array(
    '#type'        => 'fieldset',
    '#title'       => t('Accounting urls'),
    '#description' => t('URLs (links) to track accountings, use "Preview" if you need to fill more rows'),
    '#collapsible' => TRUE,
    '#tree'        => TRUE,
    '#collapsed'   => TRUE,
  );
  do {
    $form['accounting_urls'][$k] = array(
      '#type'        => 'fieldset',
      '#title'       => t('Link #').($k+1),
      '#collapsible' => FALSE,
      '#tree'        => TRUE,
      '#collapsed'   => FALSE,
      '#attributes'  => array('class'=>"cert-field"),
    );
    $form['accounting_urls'][$k]['node_id'] = array(
      '#type' => 'textfield',
      '#title' => t('Node'),
      '#size'          => 25,
      '#maxlength'     => 256,
      '#description' =>  t('Affected guifi.net node'),
      '#autocomplete_path' => 'guifi/js/select-node',
      '#default_value' => ($node->accounting_urls[$k]['node_id']),
    );
    $form['accounting_urls'][$k]['url'] = array(
      '#type' => 'textfield',
      '#title' => t('Url'),
      '#size'          => 50,
      '#maxlength'     => 256,
      '#description' =>  t('link to the accounting object'),
      '#default_value' => ($node->accounting_urls[$k]['url']),
    );
    $form['accounting_urls'][$k]['comment'] = array(
      '#type' => 'textfield',
      '#title' => t('Comment'),
      '#size'          => 256,
      '#maxlength'     => 256,
//      '#description' =>  t('link to the accounting object'),
      '#default_value' => ($node->accounting_urls[$k]['comment']),
    );
    $k++;
  } while (!empty($node->accounting_urls[$k -1]['url']));

  /*
   * Contact
   */
  $form['contact'] = array(
    '#type'        => 'fieldset',
    '#title'       => t('Contact'),
    '#collapsible' => TRUE,
    '#collapsed'   => TRUE,
  );
  if ((empty($node->notification)) or (empty($node->phone)))
    $form['contact']['#collapsed']=FALSE;
  $form['contact']['notification'] = array(
    '#type'             => 'textfield',
    '#size'             => 25,
    '#maxlength'        => 50,
    '#title'            => t('Contact'),
    '#required'         => TRUE,
    '#element_validate' => array('guifi_emails_validate'),
    '#default_value'    => $node->notification,
    '#description'      =>  t('Mailid where changes on the device will be notified, ' .
        'if many, separated by \',\'.')
  );
  $form['contact']['phone'] = array(
    '#type'             => 'textfield',
    '#size'             => 25,
    '#maxlength'        => 50,
    '#title'            => t('Phone(s)'),
    '#required'         => FALSE,
    '#default_value'    => $node->phone,
    '#description'      =>  t('Phone(s) for contacting this supplier')
  );
  $form['contact']['web_url'] = array(
    '#type'             => 'textfield',
    '#size'             => 25,
    '#maxlength'        => 128,
    '#title'            => t('Web'),
    '#required'         => FALSE,
    '#default_value'    => $node->web_url,
    '#description'      =>  t('Will be redirected if clicking on the logo')
  );
  if (empty($node->logo)) {
    $form['contact']['logo'] = array(
      '#type' => 'file',
      '#title' => t('Logo'),
      '#description' => t('Best results with 4:3 ratio and 300 width (300x225 size)'),
      '#default_value' => $node->logo,
      '#prefix' => '<table><td>',
    );
    $form['contact']['upload'] = array('#type' => 'submit', '#value' => t('Upload'),'#name'=>'Upload',
      '#executes_submit_callback' => TRUE,
      '#submit' => array('budgets_supplier_form_submit'),
      '#suffix' => '</td></table>',
    );
    $form['#attributes']['enctype'] = 'multipart/form-data';
  } else {
    $form['contact']['logo'] = array(
      '#type'=> 'hidden','#prefix' => '<table><td>','#value'=>$node->logo);
    $form['contact']['display_logo'] = array(
      '#type'=> 'item',
      '#title'=>t('Logo'),
      '#description'=>$node->logo,
      '#value'=>'<img src="/'.$node->logo.'" width="100" alt="'.$node->logo.'">',
    );
    $form['contact']['delete_logo'] = array('#type' => 'submit', '#value' => t('Delete logo'),'#name'=>'Delete',
      '#executes_submit_callback' => TRUE,
      '#submit' => array('budgets_supplier_form_submit'),
      '#suffix' => '</td></table>',
    );
  }

  /*
   * Acknowledgement on the Terms & Condition and the p2p agreement
   */
  $form['ack'] = array(
    '#type'        => 'checkbox',
    '#title'       => t('Terms & conditions agreement'),
    '#required'    => TRUE,
    '#default_value'=>$node->ack,
    '#options'     => array('0'=>'No','1'=>t('Yes, I agree')),
    '#description' => t(
       'I agree on:<br>' .
       '<ul><li>I\'m responsible for all the information I provided, that is truthfulness and available for being verified upon request. ' .
       'May be corrected or cancelled if found inaccurate or inappropriate</li>' .
       '<li>I\'ve read and understood the <a href="http://guifi.net/ComunsXOLN">Comuns XOLN peer to peer agreement</a>, ' .
       'I\'m aware that is applicable in many of the activities happening around the scope of this site ' .
       'and that the guifi.net Foundation is competent for mediating in case of dispute.</li>' .
       '<li>Beyond the <a href="http://guifi.net/ComunsXOLN">Comuns XOLN peer to peer agreement</a> but developing it, there are Best Practices ' .
       'and Ethics guidelines that also require compliance.</li>' .
       '<li>This information is provided with the solely purpose of being advertised through the listings of this web under the ' .
       '<a href="http://creativecommons.org/licenses/by-sa/3.0/">CC-BY-SA licensing</a> and be subject to the site publishing criteria.' .
       '<li>I can exercise the right of update or cancelation of this information directly by accessing this site by myself by using the web site interface' .
       '</li></ul>'
       ),
  );
  return $form;
}

function budgets_supplier_validate(&$node) {
  guifi_log(GUIFILOG_TRACE, 'function budgets_supplier_validate()', $node->op.'-'.$node->delete);

  if ($node->op==$node->delete)
    return;

  if (!$node->ack)
    form_set_error('ack',t('You should accept the Terms & Conditions to proceed'));

  if (($node->caps['caps_services']['isp'] > 2) and (empty($node->certs['tp_certs']['ISP'])))
    form_set_error('certs][tp_certs][ISP',t('You should have the NRA or equivalent certificate to operate as ISP'));


  foreach ($node->certs as $type =>$cert) {
  	foreach ($cert as $key=>$value) {
  	  if ($value != '') {
  	  	// Using a date
  	    if (date('j/n/Y',$value)==FALSE)
  	      form_set_error('certs]['.$type.']['.$key,t('Date %date must be in dd/mm/YYYY format',array('%date'=>$value)));
  	  }
  	}
  }

/*  foreach ($node->caps as $type =>$ap) {
  	foreach ($cap as $key=>$value) {
  	  if ($value != '') {
  	  	// Validate that capability is meant for volunteers
  	    if (date('j/n/Y',$value)==FALSE)
  	      form_set_error('caps]['.$type.']['.$key,t('Date %date must be in dd/mm/YYYY format',array('%date'=>$value)));
  	  }
  	}
  }
 */

  foreach ($node->zones as $k=>$zone_str) {
  	$zone=explode('-',$zone_str);
//    form_set_error('zones]['.$k,t('%zone is not a valid zone',array('%zone'=>$zone_str)));
    if ($zone_str!='') {
      $qzone = db_fetch_object(db_query('SELECT id FROM {guifi_zone} WHERE id=%d',$zone[0]));
      if (!($qzone->id))
        form_set_error('zones]['.$k,t('%zone is not a valid zone',array('%zone'=>$zone[0])));
    }
  }

  if (!(is_numeric($node->default_tax_rate)))
    form_set_error('default_tax_rate',t('%rate has to be numeric',array('%rate'=>$node->default_tax_rate)));

  if (!empty($node->web_url) and (!valid_url($node->web_url)))
      form_set_error('web_url',
        t('The URL %url is invalid. Please enter a fully-qualified URL, such as http://guifi.net/node/9876',
          array('%url'=>$value['url'])));

  foreach ($node->accounting_urls as $k => $value) {
    if (!empty($value['url'])) if (!valid_url($value['url'], TRUE)) {
      form_set_error('accounting_urls]['.$k.'][url',
        t('The URL %url is invalid. Please enter a fully-qualified URL, such as http://guifi.net/node/9876',
          array('%url'=>$value['url']))
      );
    }
    if (!empty($value['node_id'])) {
      $n = explode('-',$value['node_id']);
      if (!is_numeric($n[0]) or (guifi_get_nodename($n[0])==NULL))
          form_set_error('accounting_urls]['.$k.'][node_id',
            t('%s is not a valid node',array('%s'=>$value['node_id'])));
    }
  }

}

function budgets_supplier_form_submit($form_id, &$form_values) {
  $supplier = &$form_values['values'];

  guifi_log(GUIFILOG_TRACE, 'function budgets_supplier_submit()',$supplier);

  /*
   * Delete logo
   */
  if (isset($supplier['Delete'])) {
  	$result = file_delete($supplier['logo']);
    guifi_log(GUIFILOG_TRACE, 'function budgets_supplier_submit(DELETE)',$result);
  	$supplier['logo']=null;
    $form_values['#redirect'] = FALSE;
    $form_values['rebuild'] = TRUE;
    return;
  }

  #this leads us to sites/mysite.example.com/files/
  $dir = file_directory_path();
  $dir .= '/suppliers';
  guifi_log(GUIFILOG_TRACE, 'function budgets_supplier_submit(DIR)',$dir);

  # unlike form submissions, multipart form submissions are not in
  # $form_state, but rather in $FILES, which requires more checking
  if (isset($_FILES) && !empty($_FILES) && $_FILES['files']['size']['logo'] != 0) {

    #this structure is kind of wacky
    $name = $_FILES['files']['name']['logo'];
    $size = $_FILES['files']['size']['logo'];
    $type = $_FILES['files']['type']['logo'];

    #this is the actual place where we store the file
    $file = file_save_upload('logo', array() , $dir);
    if($file){
      drupal_set_message(t('You uploaded %name',array('%name'=>$name)));
      guifi_log(GUIFILOG_TRACE, 'file',$file->filepath);
      $supplier['logo']=$file->filepath;
     file_set_status($file,FILE_STATUS_PERMANENT);
    }
    else{
        drupal_set_message("Something went wrong saving your file.");
    }
  }
  else {
    drupal_set_message("Your file doesn't appear to be here.");
  }

  $form_values['#redirect'] = FALSE;
  $form_values['rebuild'] = TRUE;

}

function budgets_supplier_access($op, $node, $account = NULL) {
  global $user;

  if (is_numeric($node))
    $k = $node;
  else
    $k = $node->id;
  $node = node_load(array('nid' => $k));

  switch ($op) {
    case 'create':
      return user_access('create suppliers',$account);
    case 'update':
      if ($node->type == 'supplier') {
        if ((user_access('administer suppliers',$account))
          or ($node->uid == $user->uid)) {
          return TRUE;
        }
        else {
          return FALSE;
        }
      }
      else {
        return user_access('create suppliers',$account);
      }
  }
}

function budgets_supplier_get_supplier_id($uid) {
  guifi_log(GUIFILOG_TRACE, 'function budgets_supplier_get_supplier_id()', $uid);

  $sid = db_fetch_object(db_query("SELECT s.id FROM {supplier} s, {node} n WHERE n.nid=s.id AND n.uid = %d",$uid));
  return $sid->id;
}

function budgets_supplier_get_name($id){
  $matches = array();

  $string = strtoupper(arg(3));

  $qry = db_query(
    'SELECT ' .
    '  CONCAT(id, "-", title) str '.
    'FROM {supplier} ' .
    'WHERE ' .
    '  (CONCAT(id, "-", upper(title)) ' .
    '    LIKE "%'.$string.'%") ' .
    'ORDER BY title');

  $c = 0;
  while (($value = db_fetch_array($qry)) and ($c < 50)) {
    $c++;
    $matches[$value['str']] = $value['str'];
  }
  print drupal_to_js($matches);
  exit();
}

function budgets_supplier_save($node) {
  global $user;

  guifi_log(GUIFILOG_TRACE,'function budgets_save()',$node->ack);

  foreach ($node->caps as $cap1 => $cap2) {
    $elements = array();
    foreach ($cap2 as $key => $value) {
      if (($value) and (guifi_type_relation($cap1,$key,$node->role)))
        $elements[] = $key.'='.$value;
    };
    $node->$cap1 = implode(',',$elements);
  };

  foreach ($node->certs as $cap1 => $cap2) {
    $elements = array();
    foreach ($cap2 as $key => $value) {
      ($value) ? $elements[] = $key.'='.$value: NULL;
    };
    $node->$cap1 = implode(',',$elements);
  };

  $zones = array();
  foreach ($node->zones as $k=>$value) {
    if ($value!='') {
      $zone_exploded = explode('-',$value);
      $zones[] = $zone_exploded[0];
    }
  }
  $node->zones=implode(',',$zones);
  $node->zone_id=$zones[0];

  $node->self_rating = $node->sr_commitment.$node->sr_experience;
  $node->official_rating = $node->or_commitment.$node->or_experience.$node->or_trend;

  $node->accounting_urls=serialize($node->accounting_urls);

  $to_mail = $user->mail;
  $log = '';

  $sid = _guifi_db_sql(
    'supplier',
    array('id' => $node->nid),
    (array)$node,
    $log,$to_mail);

  if ($node->deleted)
    $action = t('DELETED');
  else if ($node->new)
    $action = t('CREATED');
  else
    $action = t('UPDATED');

  $subject = t('The supplier %title has been %action by %user.',
    array('%title' => $node->title,
      '%action' => $action,
      '%user' => $user->name));

  drupal_set_message($subject);

  guifi_notify(
    $to_mail,
    $subject,
    $log);
}

function budgets_supplier_insert($node) {
  $node->new = TRUE;
  $node->id = $node->nid;
  budgets_supplier_save($node);
}

function budgets_supplier_delete($node) {
  $node->deleted = TRUE;
  budgets_supplier_save($node);
}

function budgets_supplier_update($node) {
  budgets_supplier_save($node);
}

function budgets_supplier_load_explode_caps($fields,&$node) {
    foreach ($fields as $field) if ($node->$field != '') {
      $elements = explode(',',$node->$field);
//      guifi_log(GUIFILOG_BASIC,'function budgets_save 2()',$elements);
      foreach ($elements as $value)
        $node->caps[$field][strstr($value,'=',true)]=substr(strstr($value,'='),1);
//      guifi_log(GUIFILOG_BASIC,'function budgets_save 3()',$node->caps);
    };
};

function budgets_supplier_load_explode_certs($fields,&$node) {
    foreach ($fields as $field) if ($node->$field != '') {
      $elements = explode(',',$node->$field);
//      guifi_log(GUIFILOG_BASIC,'function budgets_save 2()',$elements);
      foreach ($elements as $value)
        $node->certs[$field][strstr($value,'=',true)]=substr(strstr($value,'='),1);
//      guifi_log(GUIFILOG_BASIC,'function budgets_save 3()',$node->certs);
    };
};


function budgets_supplier_load($node) {

  if (is_object($node))
    $k = $node->nid;
  else
    $k = $node;

  // $node->body=strip_tags($node->body,'<br>');

  $node = db_fetch_object(
    db_query("SELECT * FROM {supplier} WHERE id = '%d'", $k));

  if (is_null($node->id))
    return FALSE;

  $node->certs['tp_certs'] = array();
  $node->certs['guifi_certs'] = array();
  $node->caps['caps_services'] = array();
  $node->caps['caps_network'] = array();
  $node->caps['caps_project'] = array();

  budgets_supplier_load_explode_certs(
    array(
      'tp_certs',
      'guifi_certs',
      ),
      $node);
  budgets_supplier_load_explode_caps(
    array(
      'caps_services',
      'caps_network',
      'caps_project'
      ),
      $node);

  $node->zones=explode(',',$node->zones);

  $node->sr_commitment = substr($node->self_rating,0,1);
  $node->sr_experience = substr($node->self_rating,1,1);
  $node->or_commitment = substr($node->official_rating,0,1);
  $node->or_experience = substr($node->official_rating,1,1);
  $node->or_trend = substr($node->official_rating,2,1);

  $node->accounting_urls = unserialize($node->accounting_urls);

  if ($node->official_rating=='~~')
    $node->rated=FALSE;
  else
    $node->rated=TRUE;

  return $node;
}

function budgets_supplier_list_by_zone_filter($parm,$zid,$keys=NULL) {
  guifi_log(GUIFILOG_TRACE,'budgets_supplier_list_by_zone_filter',$keys);

  /*
   * Filter form
   */
  $form['filter'] = array(
    '#title'=> t('Filter').' '.$keyword,
    '#type'=>'fieldset',
    '#tree'=>false,
    '#collapsed'=>empty($keys) ? true : false,
    '#collapsible'=>true,
  );
  $form['filter']['tp_certs'] = array(
    '#type'=>'checkboxes',
    '#title'=>t('Professional certificate'),
    '#options'=> guifi_types('tp_certs'),
    '#multiple'=>true,
    '#size'=>6,
    '#default_value'=> (is_array($keys['tp_certs'])) ? $keys['tp_certs'] : array(),
//    '#attributes'=> array('class'=>"budgets-zone-form"),
  );
  $form['filter']['guifi_certs'] = array(
    '#type'=>'checkboxes',
    '#title'=>t('guifi.net certificate'),
    '#options'=> guifi_types('guifi_certs'),
    '#multiple'=>true,
    '#size'=>8,
    '#default_value'=> (is_array($keys['guifi_certs'])) ? $keys['guifi_certs'] : array(),
//    '#attributes'=> array('class'=>"budgets-zone-form"),
  );
  $form['filter']['caps_services'] = array(
    '#type'=>'checkboxes',
    '#title'=>t('services & content providers'),
    '#options'=> guifi_types('caps_services'),
    '#multiple'=>true,
    '#size'=>8,
    '#default_value'=> (is_array($keys['caps_services'])) ? $keys['caps_services'] : array(),
//    '#attributes'=> array('class'=>"budgets-zone-form"),
  );
  $form['filter']['caps_network'] = array(
    '#type'=>'checkboxes',
    '#title'=>t('network dev. & mgmt.'),
    '#options'=> guifi_types('caps_network'),
    '#multiple'=>true,
    '#default_value'=> (is_array($keys['caps_network'])) ? $keys['caps_network'] : array(),
//    '#attributes'=> array('class'=>"budgets-zone-form"),
  );
  $form['filter']['caps_project'] = array(
    '#type'=>'checkboxes',
    '#title'=>t('project development'),
    '#options'=> guifi_types('caps_project'),
    '#multiple'=>true,
    '#size'=>8,
    '#default_value'=> (is_array($keys['caps_project'])) ? $keys['caps_project'] : array(),
//    '#attributes'=> array('class'=>"budgets-zone-form"),
  );
  $form['filter']['role'] = array(
    '#type'=>'radios',
    '#title'=>t('Role'),
    '#options'=> array('volunteer'=>t('Volunteer'),'professional'=>t('Professional')),
    '#multiple'=>true,
    '#size'=>2,
    '#default_value'=>$keys['role'][0],
//    '#attributes'=> array('class'=>"budgets-zone-form"),
  );
  $form['filter']['zone_id'] = array('#type'=>hidden,'#value'=>$zid);
  $form['filter']['title'] = array(
    '#type'=>'textfield',
    '#size'=>60,
    '#title'=>t('Keyword'),
    '#description'=>t('Name contains (use single keyword)'),
    '#default_value'=>$keys['title'][0],
    '#prefix'=> '<table><td>',
  );
  $form['filter']['submit'] = array(
    '#type'=>'submit',
    '#title'=>t('Press to proceed'),
    '#value'=>t('Search'),
    '#suffix'=> '</td></table>',
  );

  return $form;
}

function budgets_supplier_list_by_zone_filter_submit($form_id, &$form_values) {
  $v=$form_values['values'];
  guifi_log(GUIFILOG_TRACE,'budgets_supplier_list_by_zone_filter_submit',$v);

  $sv = array();

  $v['tp_certs']=array_diff($v['tp_certs'],array(0));
  $v['guifi_certs']=array_diff($v['guifi_certs'],array(0));
  $v['caps_services']=array_diff($v['caps_services'],array(0));
  $v['caps_network']=array_diff($v['caps_network'],array(0));
  $v['caps_project']=array_diff($v['caps_project'],array(0));
  if (!empty($v['title']))         $sv[] = 'title='.$v['title'];
  if (!empty($v['role']))          $sv[] = 'role='.$v['role'];
  if (!empty($v['tp_certs']))      $sv[] = 'tp_certs='.implode(',',$v['tp_certs']);
  if (!empty($v['guifi_certs']))   $sv[] = 'guifi_certs='.implode(',',$v['guifi_certs']);
  if (!empty($v['caps_services'])) $sv[] = 'caps_services='.implode(',',$v['caps_services']);
  if (!empty($v['caps_network']))  $sv[] = 'caps_network='.implode(',',$v['caps_network']);
  if (!empty($v['caps_project']))  $sv[] = 'caps_project='.implode(',',$v['caps_project']);

  $s = implode(',',$sv);

  guifi_log(GUIFILOG_TRACE,'budgets_supplier_list_by_zone_submit',$sv);

  drupal_goto('node/'.$v['zone_id'].'/suppliers/'.$s);
}

function budgets_supplier_list_by_zone($zone,$params = NULL) {

  guifi_log(GUIFILOG_TRACE,'budgets_supplier_list_by_zone (PARAMS)',$params);

  $zroot = guifi_bg_zone_root();
  $vars = array();

  if (($zone->id==0) or (empty($zone->id))) {
    $zone->id=$zroot;
  }

  if ($params) {
    $p=explode(',',$params);
    foreach ($p as $v) {
      $v=explode('=',$v);
      if (!empty($v[1]))
        $vars[$v[0]]=explode('|',$v[1]);
    }
  }

  $output = drupal_get_form('budgets_supplier_list_by_zone_filter',$zone->id,$vars);

  $parents = array();

  if ($zone->id==$zroot) {
    // listing root zone: don't have to list the parents or childs'
    $where = ''; //list all
  } else {
 	guifi_log(GUIFILOG_TRACE,'list_by_zone (zones)',guifi_zone_get_parents($zone->id));
  	// other zones, parents and childs should be included, but
  	// excluding root
    $zlist = array_diff(guifi_zone_childs_and_parents($zone->id),
       array(0,$zroot));
    $where = 'AND (s.zone_id IN ('.implode(',',$zlist).') ';
    foreach ($zlist as $z)
      $where .= "OR CONCAT(',',s.zones,',') LIKE '%,".$z.",%' ";
    $where.=') ';
  }

  guifi_log(GUIFILOG_TRACE,'list_suppliers_by_zone (zones)',$vars);

  $svars = array();
  foreach ($vars as $k => $v) {
    foreach ($v as $p)
      if (!empty($p))
        if ($k == 'title')
          $svars[] = "upper(s.title) like '%".strtoupper($p)."%' ";
        else
          $svars[] = $k." LIKE '%".$p."%' ";
  }
  if (count($svars)>0)
    $swhere = ' AND ('.implode(' AND ',$svars).') ';
  else
    $swhere = '';

  $qquery =
    'SELECT s.id ' .
    'FROM {supplier} s, {node} n ' .
    'WHERE s.id=n.nid ' .
    ' AND n.status=1 '.
    $swhere.
    $where.
    // Order by rating, creating a code for sort the trend:
    //  ''+''=0, ''=1, '-'=2
    'ORDER BY ' .
    '  REPLACE(' .
    '    REPLACE(' .
    '      IF(' .
    '        LENGTH(TRIM(s.official_rating)=2),' .
    '        CONCAT(TRIM(s.official_rating),"1"),' .
    '        s.official_rating),' .
    '      "-",2),' .
    '    "+","0"),' .
    ' role, rand() ';
  guifi_log(GUIFILOG_TRACE,'list_by_zone (suppliers query)',$qquery);
  $pager = pager_query($qquery,50
  //  variable_get('default_nodes_main', 25)
  );
  // $output = '';
  while ($s = db_fetch_object($pager)) {
    $supplier = node_load(array('nid' => $s->id));
    $output .= node_view($supplier, TRUE, FALSE, TRUE);
  }
  $output .= theme('pager', NULL, variable_get('default_nodes_main', 10));

  drupal_set_breadcrumb(guifi_zone_ariadna($zone->id,'node/%d/view/suppliers'));
//  $output .= theme_pager(NULL, variable_get("guifi_pagelimit", 50));
//  $node = node_load(array('nid' => $zone->id));
//  $output .= theme_links(module_invoke_all('link', 'node', $node, FALSE));
  print theme('page',$output, FALSE);
  return;
}

function budgets_supplier_view($node, $teaser = FALSE, $page = FALSE) {


  if (!isset($node->nid))
    $node = node_load(array('nid' => $node->id));
  if ($node->sticky)
    $node->sticky=0;

  guifi_log(GUIFILOG_TRACE, 'function budgets_supplier_view(teaser)',$teaser);


  node_prepare($node, $teaser);

  if ($teaser) {
    $body = node_teaser($node->body,TRUE,($node->rated==true)? 600 : 300);
    $body = strip_tags($body,'<br> ');
    if ($node->rated)  {
      if (!empty($node->logo))
        $img = '<img class="supplier-logo" src="/'.$node->logo.'" width="150">';
        if (!empty($node->web_url))
          $img = '<a href="'.$node->web_url.'">'.$img.'</a>';
        $body = "<div class=\"supplier-rated\">".$img.$body."</div>";
    }
    $node->content['body']['#value'] = $body;
  }

  if ($node->rated)
    $node->content['body']['#value'] =
      '<p class="rating" >'.$node->official_rating.'</p>'.
      $node->content['body']['#value'];


  $node->content['header'] = array(
    '#value' => theme_budgets_supplier_header($node, $teaser),
    '#weight' => -10
  );

  if ($page) {
    drupal_set_breadcrumb(guifi_zone_ariadna($node->zone_id,'node/%d/view/suppliers'));

    $body = $node->content['body']['#value'];
    if (!empty($node->logo))
        $img = '<img class="supplier-logo" src="/'.$node->logo.'" width="200">';
        if (!empty($node->web_url))
          $img = '<a href="'.$node->web_url.'">'.$img.'</a>';

    // Quotes
    //to-do

    // Accounting
    guifi_log(GUIFILOG_TRACE, 'function budgets_supplier_view(accounting)',$node->accounting_urls);
    if (((count($node->accounting_urls)) > 1 ) and (!empty($node->accounting_urls[0]['url']))) {
      // There are accounting urls
      $rows = array();

      foreach ($node->accounting_urls as $value) {
        guifi_log(GUIFILOG_TRACE, 'function budgets_supplier_view(value)',$value);
        if (!empty($value['url']))
          $rows[] = array(
            array('data'=>$value['node_id']),
            array('data'=>'<a href="'.$value['url'].'">'.$value['url'].'</a>'),
            array('data'=>$value['comment']),
          );
      }
      $headers = array(
        array('data'=>t('Node')),
        array('data'=>t('Url')),
        array('data'=>t('Comment')),
      );
      $body .= '<br><hr><h2>'.t('Accounting').'</h2>'.theme('table',$headers,$rows);
    }

    $node->content['body']['#value'] = $img.' '.$body;
  }

  $node->content['footer'] = array(
    '#value' => theme_budgets_supplier_footer($node, $teaser),
    '#weight' => 10
  );

  return $node;

// format antic
  $node = node_prepare($node, $teaser);
  $output = '';

  $qquotes = pager_query(
    sprintf('SELECT id ' .
    'FROM {supplier_quote} ' .
    'WHERE supplier_id = %d ' .
    'ORDER BY title, partno, id',
    $node->nid),
    variable_get('default_nodes_main', 10)
  );
  $output .= '<h3>'.t('Contact information:').'<h3 />'.
    t('Phone').':'.$node->phone.' Email: '.l($node->notification);


  if (!$teaser) {
    $output .= '<br<br><hr><h2>'.t('Quotes').'</h2>';
    while ($quote = db_fetch_object($qquotes)) {;
      $output .= node_view(node_load(array('nid' => $quote->id)), TRUE, FALSE);
    }
    (empty($quote)) ? $output .= t('No quotes available') : NULL;

    $node->content['quotes'] = array(
      '#value'=> $output.
         theme('pager', NULL, variable_get('default_nodes_main', 10)),
      '#weight' => 1,
    );
  }

  return $node;
}

function budgets_supplier_get_suppliername($id) {
  $node = db_fetch_object(db_query("SELECT s.title name FROM {supplier} s WHERE s.id=%d",$id));
  return $node->name;
//  return guifi_to_7bits($node->name);
}

function theme_budgets_supplier_header($node, $teaser) {

  $output = '<div class="suppliers-help-rating">' .
  		'<small><a href="/comment/reply/'.$node->nid.'#comment-form">'.t('User rating: give your feedback & vote!').'</a></small> - '.t('Zone(s)').': ';
  foreach ($node->zones as $k=>$value) {
    $output .= ' > '.guifi_zone_l($value);
  }
  $output .= '</div><br>';

  return $output;
}

function theme_budgets_supplier_footer($node, $teaser) {
  $output = '<br></hr><em>'.strtoupper(t($node->role)).'</em><br>';

  $max=0;
  ($max<(count($node->certs['tp_certs']))) ? $max = count($node->certs['tp_certs'])         : NULL;
  ($max<(count($node->certs['guifi_certs']))) ? $max = count($node->certs['guifi_certs'])   : NULL;
  ($max<(count($node->caps['caps_services']))) ? $max = count($node->caps['caps_services']) : NULL;
  ($max<(count($node->caps['caps_network']))) ? $max = count($node->caps['caps_network'])   : NULL;
  ($max<(count($node->caps['caps_project']))) ? $max = count($node->caps['caps_project'])   : NULL;

  $tp_certs = guifi_types('tp_certs');
  $guifi_certs = guifi_types('guifi_certs');
  $caps_services = guifi_types('caps_services');
  $caps_network = guifi_types('caps_network');
  $caps_project = guifi_types('caps_project');

  $certs = array();
  if ($teaser) {
  	if ((count($node->certs))>0) {
      $certs_types=array_merge($tp_certs,$guifi_certs);
  	  foreach ($node->certs as $kcert=>$cert_values) {
  	    foreach ($cert_values as $k=>$value) {
  	      $certs[] = $certs_types[$k];
  	    }
  	  }
      $output .= t('Certifications').':<small> '.implode(', ',$certs).'</small>';
  	}

  	if ((count($node->caps))>0) {
      $caps_types=array_merge($caps_services,$caps_network,$caps_project);
  	  foreach ($node->caps as $kcap=>$cap_values) {
  	    foreach ($cap_values as $k=>$value) {
          guifi_log(GUIFILOG_TRACE, 'caps',$kcap.' '.$k.' '.$node->role);
          if (guifi_type_relation($kcap,$k,$node->role))
  	        $caps[] = $caps_types[$k];
  	    }
  	  }
  	  if (count($caps)>0)
        $output .= ' '.t('Capabilities').':<small> '.implode(', ',$caps).'</small>';
  	}
  } else {
  	$output.= '<small />';


  	for ($i=0;$i<$max;$i++) {
      $rows[$i] = array(
        array('data'=>$tp_certs[key($node->certs['tp_certs'])].' '.current($node->certs['tp_certs'])),
        array('data'=>$guifi_certs[key($node->certs['guifi_certs'])].' '.current($node->certs['guifi_certs'])),
        array('data'=>theme_budgets_supplier_showcap($caps_services[key($node->caps['caps_services'])],
          current($node->caps['caps_services']))),
        array('data'=>theme_budgets_supplier_showcap($caps_network[key($node->caps['caps_network'])],
          current($node->caps['caps_network']))),
        array('data'=>theme_budgets_supplier_showcap($caps_project[key($node->caps['caps_project'])],
          current($node->caps['caps_project']))),
      );

      next($node->certs['tp_certs']);
      next($node->certs['guifi_certs']);
      next($node->caps['caps_services']);
      next($node->caps['caps_network']);
      next($node->caps['caps_project']);
    }

    $headers = array(
      array('data'=>t('Professional certifications')),
      array('data'=>t('guifi.net certifications')),
      array('data'=>t('Services')),
      array('data'=>t('Network infraestructure')),
      array('data'=>t('Projects')),
    );

  	$output .= theme('table', $headers, $rows);
  	/*
  	 * Contact
  	 */
  	$contact .= t('Email contact: ').'<a href="mailto:'. $node->notification .'">'. $node->notification .'</a>';
  	if (!empty($node->phone))
  	  $contact .= ' - '.t('Phone(s): ').$node->phone;

  	/*
  	 * Address
  	 */
  	$address = $node->address1;
  	if (!empty($node->address2))
  	  $address .= ','.$node->address2;
  	$address .= ' '.$node->postal_code.' '.$node->city.' '.$node->country;
  	if (!empty($address))
      $output .= theme('box', t('Contact'), $contact.'<hr />'.$address);
    else
      $output .= theme('box', t('Contact'), $contact);
  }

  return $output;
}

  function theme_budgets_supplier_showcap($cap,$skill) {
  	if (empty($cap))
  	  return;

    for ($i=1; $i<5; $i++)
      if ($i <= $skill)
        $output .= '<div class="capson"></div> ';
      else
        $output .= '<div class="capsoff"></div> ';
    $output .= $cap;
    return $output;
  }

function budgets_supplier_list_budgets_by_supplier($supplier,$params=null) {


  guifi_log(GUIFILOG_TRACE,'list_budgets_by_supplier (supplier)',$supplier);


  $btypes=guifi_types('budget_type');
  $bstatus=guifi_types('budget_status');

  if (empty($params)) {
    $vars['details'][0]='detailed';
    $vars['url'][0]='budgets';
    $vars['types']=array_keys($btypes);
    $vars['status']=array_keys($bstatus);
    $vars['from']=array_combine(array('year','month','day'),explode(' ',date('Y n j',time()-(60*60*24*30*12))));
    $vars['to']=array_combine(array('year','month','day'),explode(' ',date('Y n j')));
  } else {
    $p=explode(',',$params);
    foreach ($p as $v) {
      $v=explode('=',$v);
      if (($v[0]=='from') or ($v[0]=='to'))
        $vars[$v[0]]=array_combine(array('year','month','day'),explode('|',$v[1]));
      else
        $vars[$v[0]]=explode('|',$v[1]);
    }
  }

  guifi_log(GUIFILOG_TRACE,'budgets_by_supplier (zone)',$vars);

  $output = drupal_get_form('budgets_list_form',$supplier->id,$vars);

  $where = '';

  if ($vars['types'])
    $where .= " AND b.budget_type in ('".implode("','",$vars['types'])."') ";
  if ($vars['status'])
    $where .= " AND b.budget_status in ('".implode("','",$vars['status'])."') ";

  $f = mktime(0,0,0,$vars['from']['month'],$vars['from']['day'],$vars['from']['year']);
  $where .= sprintf(' AND IFNULL(b.accdate,b.expires) > %d',$f);
  $t = mktime(23,59,59,$vars['to']['month'],$vars['to']['day'],$vars['to']['year']);
  $where .= sprintf(' AND IFNULL(b.accdate,b.expires) <= %d ',$t);


  $qquery =
    'SELECT b.id, b.accdate ' .
    'FROM {budgets} b ' .
    'WHERE b.supplier_id=' .$supplier->id.' '.
    $where.
    // Order by rating, creating a code for sort the trend:
    //  ''+''=0, ''=1, '-'=2
    'ORDER BY b.accdate desc ';

  guifi_log(GUIFILOG_TRACE,'list_budgets_by_supplier (budgets query)',$qquery);

  $query = db_query($qquery);
  $subtotals = array();
  $time_subtotals = array();

  while ($s = db_fetch_object($query)) {
    $budget = node_load(array('nid' => $s->id));
    $subtotals[$budget->supplier_id] += $budget->total;
    if (is_null($s->accdate))
      $tdate=$budget->expires;
    else
      $tdate=$budget->accdate;
    $time_subtotals[date('Y',$tdate)][date('n',$tdate)] [$budget->supplier_id] += $budget->total;

  	if (budgets_access('view',$budget))
  	  if ($vars['details'][0]=='detailed')
        $doutput .= node_view($budget, TRUE, FALSE, TRUE);
  }

  $output .= budgets_list_totals($subtotals).
    budgets_list_monthly_totals($subtotals,$time_subtotals,$vars).
    $doutput;
  //theme('pager', NULL, variable_get('default_nodes_main', 10));

//  drupal_set_breadcrumb(guifi_zone_ariadna($zone->id,'node/%d/view/suppliers'));
//  $output .= theme_pager(NULL, variable_get("guifi_pagelimit", 50));
//  $node = node_load(array('nid' => $zone->id));
//  $output .= theme_links(module_invoke_all('link', 'node', $node, FALSE));
  print theme('page',$output, FALSE);
  return;
}

function budgets_supplier_fundings($supplier,$type, $pager = 50) {


  guifi_log(GUIFILOG_TRACE,'budgets_supplier_fundings (supplier)',$type);

  if ($type != 'all')
    $swt = ' AND subject_type = "'.$type.'" ';
  $qquery =
    'SELECT * ' .
    'FROM {guifi_funders} ' .
    'WHERE supplier_id=' .$supplier->id.' '.$swt.
    'ORDER BY timestamp_created desc ';
  guifi_log(GUIFILOG_TRACE,'budgets_supplier_fundings (budgets query)',$qquery);
 $pager = pager_query($qquery,
    variable_get('default_nodes_main', $pager)
  );
  $output = '';
  $rows = array();
  while ($s = db_fetch_object($pager)) {
    guifi_log(GUIFILOG_TRACE,'budgets_supplier_fundings (row)',$s);
    switch ($s->subject_type) {
      case 'location':
        $n=guifi_get_nodename($s->subject_id);
        $l='node/'.$s->subject_id;
        break;
      case 'device':
        $n=guifi_get_devicename($s->subject_id);
        $l='guifi/device/'.$s->subject_id.'/view';
        break;
    }
    if ($type=='all')
      $n .= ' ('.t($s->subject_type).')';
    $u=user_load($s->user_created);
    $rows[] = array(
      l($s->subject_id.'-'.$n,$l),
      $s->comment,
      l(t('by').' '.$u->name,'user/'.$s->user_created),
      format_date($s->timestamp_created),
    );
  }
  if (count($rows)==0)
    $rows[] = array(array('data'=>t('none'),'colspan'=>4));

  $header = array(
    t($type),
    t('comment'),
    t('created'));
  $output = theme('table',$header,$rows);
  $output .= theme('pager', NULL, $pager);

  print theme('page',$output, FALSE);
  return;
}

function budgets_supplier_sla($supplier,$type, $pager = 50) {


  guifi_log(GUIFILOG_TRACE,'budgets_supplier_sla (supplier)',$type);

  if ($type != 'all')
    $swt = ' AND subject_type = "'.$type.'" ';
  $qquery =
    'SELECT * ' .
    'FROM {guifi_maintainers} ' .
    'WHERE supplier_id=' .$supplier->id.' '.$swt.
    'ORDER BY timestamp_created desc ';
  guifi_log(GUIFILOG_TRACE,'budgets_supplier_sla (budgets query)',$qquery);
 $pager = pager_query($qquery,
    variable_get('default_nodes_main', $pager)
  );
  $output = '';
  $rows = array();
  while ($s = db_fetch_object($pager)) {
    guifi_log(GUIFILOG_TRACE,'budgets_supplier_sla (row)',$s);
    switch ($s->subject_type) {
      case 'location':
        $n=guifi_get_nodename($s->subject_id);
        $l='node/'.$s->subject_id;
        break;
      case 'zone':
        $n=guifi_get_zone_name($s->subject_id);
        $l='node/'.$s->subject_id;
        break;
      case 'device':
        $n=guifi_get_devicename($s->subject_id);
        $l='guifi/device/'.$s->subject_id.'/view';
        break;
    }
    if ($type=='all')
      $n .= ' ('.t($s->subject_type).')';
    $u=user_load($s->user_created);
    $rows[] = array(
      l($s->subject_id.'-'.$n,$l),
      $s->commitment,
      $s->sla,
      $s->sla_resp,
      $s->sla_fix,
 //     $s->comment,
      l(t('by').' '.$u->name,'user/'.$s->user_created),
      format_date($s->timestamp_created),
    );
  }
  if (count($rows)==0)
    $rows[] = array(array('data'=>t('none'),'colspan'=>8));

  $header = array(
    t($type),
    t('type'),
    t('SLA'),
    t('resp.'),
    t('fix.'),
//    t('comment'),
    t('created'),
    null,);
  $output = theme('table',$header,$rows);
  $output .= theme('pager', NULL, $pager);

  print theme('page',$output, FALSE);
  return;
}


?>

<?php
/*
 * Created on 12/08/2008 by rroca
 *
 * function for manage supplier quotes
 */

function budgets_quote_help($path, $arg) {
  if ($path == 'admin/help#budgets_quote') {
    $txt = 'A quote is whatever component of a service or material which can be' .
        ' selected by users as an item of a budget or proposal';
    $replace = array();
    return '<p>'.t($txt,$replace).'</p>';
  }
}

function budgets_quote_add($provider) {
  global $user;

  $types = node_get_types();
  $type = 'supplier_quote';
  // Initialize settings:
  $node = array(
    'uid' => $user->uid,
    'name' => (isset($user->name) ? $user->name : ''),
    'type' => $type,
    'language' => '',
    'supplier_id' => $provider->id);

  drupal_set_title(t('Create @name', array('@name' => $types[$type]->name)));
  return drupal_get_form($type .'_node_form', $node);
}

function budgets_quote_add_batch($provider) {

  guifi_log(GUIFILOG_TRACE, 'function budgets_quote_add_batch(provider)',$provider);

  $output = drupal_get_form('budgets_quote_add_batch_form','batch_quotes',$provider);

  return $output;
}

function budgets_quote_add_batch_form(&$form_state, $from = NULL, $provider) {
  guifi_log(GUIFILOG_TRACE, 'function budgets_quote_add_batch_form(provider)',$provider);
  /*
   * Provider
   */
  $form['provider'] = array(
    '#type' => 'item',
    '#title' => t('Supplier'),
    '#disabled' => true,
    '#description' => t('Quotes will be loaded to this provider'),
    '#value' => l($provider->id.'-'.$provider->title,'node/'.$provider->id),
    '#prefix' => '<table><td>',
  );
  $form['supplier'] = array(
    '#type' => 'hidden',
    '#value' => $provider->id.'-'.$provider->title,
  );

  /*
   * File format
   */
  $form['format'] = array(
    '#type'=>'fieldset',
    '#title'=>t('File format'),
    '#description'=>t('Describe the mapping between the source and the target'),
    '#collapsible'=>true,
    '#collapsed'=>false,
    '#tree'=>false,
    '#attributes'=>array('class'=>'format_upload'),
//    '#attributes'=>array('class'=>'quote'),
  );

  $form['format']['delimiter'] = array(
    '#type'=> 'select',
    '#title'=>t('Delimiter'),
    '#Description'=>t('Field delimiter character'),
    '#options'=> array(
      ';'=>'semi-colon (;)',
      ','=>'comma (,))',
      ':'=>'colon (:)',
      '.'=>'dot (,)',
      '|'=>'pipe (|)',
      '·'=>'middle-dot (#)',
    ),
  );
  switch (variable_get("budget_expires", '1q')) {
      case '1w': $dexp = mktime(0, 0, 0, date("m"),  date("d")+7,  date("Y")); break;
      case '2w': $dexp = mktime(0, 0, 0, date("m"),  date("d")+14,  date("Y")); break;
      case '1m': $dexp = mktime(0, 0, 0, date("m")+1,  date("d"),  date("Y")); break;
      case '2m': $dexp = mktime(0, 0, 0, date("m")+2,  date("d"),  date("Y")); break;
      case '1q': $dexp = mktime(0, 0, 0, date("m")+3,  date("d"),  date("Y")); break;
      case '4m': $dexp = mktime(0, 0, 0, date("m")+4,  date("d"),  date("Y")); break;
      case '1h': $dexp = mktime(0, 0, 0, date("m")+6,  date("d"),  date("Y")); break;
      case '1y': $dexp = mktime(0, 0, 0, date("m"),  date("d"),  date("Y")+1); break;
    }
  list(
      $arrexpires['year'],
      $arrexpires['month'],
      $arrexpires['day']) = explode(',', date('Y,n,j', $dexp));

  $form['format']['skip'] = array(
    '#type'=> 'textfield',
    '#size'=>4,
    '#maxlength'=>10,
    '#title'=>t('Skip'),
    '#description'=>t('From the beginning'),
    '#default_value'=>'0',
  );
  $form['format']['arrexpires'] = array(
    '#type' => 'date',
    '#title' => t('Expiration'),
    '#default_value' => $arrexpires,
    '#description' => t("Date when the quotes will expire"),
    '#required' => TRUE,
  );


  /*
   * Field mapping
   */
  $fields=array(
    'partno'=>t('Part Number'),
    'title'=>t('Title'),
    'body'=>t('Body'),
    'tax'=>t('Tax').'%',
    'cost'=>t('Cost'),
  );
  $title=t('Source');
  $options=array(
    ''=>t('Not present'),
    0=>t('First (1st)'),
    1=>t('Second (2nd)'),
    2=>t('Third (3rd)'),
    3=>t('Fourth (4th)'),
    4=>t('Fiveth (5th)'),
    5=>t('Sexth (6th)'),
    6=>t('Seventh (7th)'),
    7=>t('Eighth (8th)'),
    8=>t('Nineth (9th)'),
    9=>t('Tenth (10th)'),
  );
  $count = 0; $totalv = count($fields);
  foreach ($fields as $field=>$value) {
  	$prefix = ''; $suffix = '';
  	if ($count == 0)
  	  $prefix = '<div class=certs><table><tr><th>'.
        $title.'</th><th>'.
        t('Targets to').'</th></tr>';
  	if ($count == ($totalv - 1))
	  $suffix = '</table></div>';
  	$form['format']['caps_services'][$field] = array(
      '#type' => 'select',
      '#options'=>$options,
      '#default_value' => $count,
      '#attributes'=>array('class'=>"cert-field"),
      '#prefix' => $prefix.'<tr><td>',
      '#suffix' =>  '</td><td>'.$value.'</td>'.$suffix,
    );
    $count++;
  }

  /*
   * File to upload
   */
  $form['csv_file'] = array(
    '#type' => 'file',
    '#title' => t('CSV File'),
    '#description' => t('Source containing the quotes in CSV format to be uploaded'),
    '#default_value' => null,
    '#prefix' => '<table><td>',
    );
  $form['upload'] = array('#type' => 'submit', '#value' => t('Upload'),'#name'=>'Upload',
    '#executes_submit_callback' => TRUE,
    '#submit' => array('budgets_quote_batch_upload'),
    '#suffix' => '</td></table>',
    );
  $form['#attributes']['enctype'] = 'multipart/form-data';

  $form['#redirect'] = 'node/'.$provider->id.'/quotes';

  return $form;
}


function budgets_quote_form(&$node,&$param) {
  $type = node_get_types('type',$node);

  if (!empty($param['values'])) {
     $node = (object)$param['values'];
  }

  guifi_log(GUIFILOG_TRACE,'function budgets_quote_form()',$node);

  $type = node_get_types('type',$node);

  if (($type->has_title)) {
    $form['title'] = array(
      '#type' => 'textfield',
      '#title' => check_plain($type->title_label),
      '#required' => TRUE,
      '#default_value' => $node->title,
    );
  }

  if (isset($node->supplier))
    $form['supplier'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => t('Supplier'),
      '#description' => t('Supplier for this quote.'),
      '#default_value' => $node->supplier,
      '#autocomplete_path'=> 'budgets/js/select-supplier',
    );
  else {
    $suppliers = array();
    $qsup = db_query(
      'SELECT id, title ' .
      'FROM {supplier} ' .
      'ORDER BY title');
    while ($sup = db_fetch_object($qsup)) {
      if (!user_access('administer suppliers')) {
        if (budgets_supplier_access('update',$sup->id))
          $suppliers[$sup->id] = $sup->title;
      } else
        $suppliers[$sup->id] = $sup->title;
    }

    $form['supplier_id'] = array(
      '#type' => 'select',
      '#required' => TRUE,
      '#title' => t('Supplier'),
      '#description' => t('Supplier for this quote.'),
      '#default_value' => $node->supplier_id,
      '#options' => $suppliers,
    );
  }

  /*
   * Quote Properties
   */
  $form['props'] = array(
      '#type'=>'fieldset',
      '#title'=>t('Quote properties'),
      '#collapsible'=>false,
      '#collapsed'=>false,
      '#tree'=>false,
      '#attributes'=>array('class'=>'quote'),
  );

  $form['props']['partno'] = array(
    '#type' => 'textfield',
    '#required' => TRUE,
    '#size' => 60,
    '#maxlength' => 60,
    '#title' => t('Part number'),
    '#description' => t('Part number/Code to identify this quote.'),
    '#default_value' => $node->partno,
  );

  $form['props']['tax'] = array(
    '#type' => 'textfield',
    '#title' => t('Tax'),
    '#size' => 3,
    '#required' => TRUE,
    '#maxlength' => 3,
    '#attributes' => array('' .
        'class' => 'number required',
        'min' => 0),
    '#default_value' => $node->tax,
    '#description' => t('Tax (%) to be applied to this quote.<br> 0 means tax included'),
  );
  $form['props']['cost'] = array(
    '#type' => 'textfield',
    '#title' => t('Cost'),
    '#size' => 12,
    '#required' => TRUE,
    '#maxlength' => 15,
    '#attributes' => array('' .
        'class' => 'number required',
        'min' => 1),
    '#default_value' => $node->cost,
    '#description' => t('Quoted value (cost) for this quoted item.'),
  );
  $form['props']['arrexpires'] = array(
    '#type' => 'date',
    '#title' => t('Expiration'),
    '#default_value' => $node->arrexpires,
    '#description' => t("Date when this quote will expire"),
    '#required' => TRUE,
  );

  if (($type->has_body)) {
    $form['body_field'] = node_body_field(
      $node,
      $type->body_label,
      $type->min_word_count
    );
  }

  return $form;
}

function budgets_quote_batch_upload($form_id, &$form_values) {
  global $user;

  $fupload = &$form_values['values'];

  guifi_log(GUIFILOG_TRACE, 'function budgets_quote_batch_upload()',$fupload);

  #this leads us to sites/mysite.example.com/files/
  $dir = file_directory_path();

  $sup = explode('-',$fupload['supplier']);

  # unlike form submissions, multipart form submissions are not in
  # $form_state, but rather in $FILES, which requires more checking
  if (isset($_FILES) && !empty($_FILES) && $_FILES['files']['size']['csv_file'] != 0) {

    #this structure is kind of wacky
    $name = $_FILES['files']['name']['csv_file'];
    $size = $_FILES['files']['size']['csv_file'];
    $type = $_FILES['files']['type']['csv_file'];

    #this is the actual place where we store the file
    $file = file_save_upload('csv_file', array() , $dir);
    if($file){
      drupal_set_message(t('You uploaded %name',array('%name'=>$name)));
      guifi_log(GUIFILOG_TRACE, 'file',$file->filepath);

      /*
       * Parsing the uploaded file
       */
      $errors = 0;
      $loaded = 0;

      $fcsv = fopen($file->filepath, "r");
      $row=0;
      while (($data = fgetcsv($fcsv,4096,$fupload['delimiter'])) !== FALSE) {

        guifi_log(GUIFILOG_TRACE, 'data',$data);
        $row++;
        if ($row <= $fupload['skip'])
          continue;


        $quote = budgets_quote_load_partno($sup[0].':'.$data[$fupload['partno']]);
        guifi_log(GUIFILOG_TRACE, 'quote',$quote);

        if ($quote==false)
          $quote->created = time();
        $quote->changed = time();
        $quote->status = 1;
        $quote->promote = 0;
        $quote->sticky = 0;
        $quote->uid=$user->uid;
        $quote->type = 'supplier_quote';
        $quote->comment=1;
        $quote->supplier = $fupload['supplier'];
        $quote->partno = $sup[0].':'.$data[$fupload['partno']];
        $quote->title = $data[$fupload['title']];
        $quote->body = $data[$fupload['body']];
        $quote->tax = $data[$fupload['tax']];
        $quote->cost = $data[$fupload['cost']];
        $quote->arrexpires = $fupload['arrexpires'];
        if (is_null($quote->tax ==0))
           $quote->tax = 0;

        guifi_log(GUIFILOG_TRACE, 'quote',$quote);

        node_save($quote);

        if (!$quote->nid) {
          drupal_set_message(t('Quote was NOT saved'));
          guifi_log(GUIFILOG_TRACE, 'quote',$quote);
        }
      }
      fclose($fcsv);

      file_delete($file->filepath);
    }
    else{
        drupal_set_message("Something went wrong saving your file.");
    }
  }
  else {
    drupal_set_message("Your file doesn't appear to be here.");
  }

}

function budgets_quote_validate($node, &$form) {

  guifi_log(GUIFILOG_TRACE, 'quote validate',$node);

  // validate unique partno
  if (!empty($node->partno)) {
    if ($node->nid)
      $sql = sprintf('SELECT count(partno) partno ' .
                     'FROM {supplier_quote} ' .
                     'WHERE partno IS NOT NULL ' .
                     '  AND partno ="%s" ' .
                     '  AND id != %d',
             $node->partno,$node->nid);
    else
      $sql = sprintf('SELECT count(partno) partno ' .
                     'FROM {supplier_quote} ' .
                     'WHERE partno IS NOT NULL ' .
                     '  AND partno ="%s"',
             $node->partno);
    $count = db_fetch_object(db_query($sql));
    if ($count->partno){
      form_set_error('partno', t('Partno %partno already exists.',
        array('%partno' => $element['#value'])));
    }
  }

  // validate supplier exists
  if (isset($node->supplier)) {
    $sup = explode('-',$node->supplier);
    $node->supplier_id = $sup[0];
    $errfield = 'supplier';
  } else
    $errfield = 'supplier_id';

  $count = db_fetch_object(db_query(
    'SELECT count(id) supplier ' .
    'FROM {supplier} ' .
    'WHERE id=%d',
    $node->supplier_id));

  if (!$count->supplier)
    form_set_error($errfield, t('Supplier does not exist.'));

  // validate is privileged for using this supplier
  if (!budgets_supplier_access('update',$node->supplier_id))
    form_set_error($errfield, t('Only can add quotes to owned suppliers.'));

  if (!is_numeric($node->tax))
    form_set_error('tax', t('Tax rate must be numeric.'));
  if (!is_numeric($node->cost))
    form_set_error('cost', t('Cost must be numeric.'));

}

function budgets_quote_access($op, $node, $account = NULL) {
  global $user;

  $node = node_load(array('nid' => $node->id));
  switch ($op) {
    case 'create':
      return user_access('create suppliers',$account);
    case 'update':
      if ($node->type == 'supplier_quote') {
        if ((user_access('administer suppliers',$account))
          || ($node->uid == $user->uid)) {
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

function budgets_quote_prepare(&$node) {

  if (empty($node->expires))
    $node->expires = mktime(0, 0, 0, date("m"),  date("d"),  date("Y")+1);

  list(
    $node->arrexpires['year'],
    $node->arrexpires['month'],
    $node->arrexpires['day']) = explode(',',date('Y,n,j',$node->expires));

  // load a $node->supplier text field in case that is an administrator and
  // there is more than 10 possible choices

  if (!user_access('administer suppliers'))
    return;

  $ns = db_fetch_object(db_query(
    'SELECT count(*) suppliers ' .
    'FROM {supplier}'));
  if ($ns->suppliers > 10)
    if (!empty($node->supplier_id)) {
      $s = node_load(array('nid' => $node->supplier_id));
      $node->supplier = $s->nid.'-'.$s->title;
    } else
      $node->supplier = '';
}

function budgets_quote_save($node) {
  global $user;

  $to_mail = $user->mail;
  $log = '';

  if (isset($node->supplier)) {
    $sup = explode('-',$node->supplier);
    $node->supplier_id = $sup[0];
  }

  $node->expires =
    mktime(0,0,0,
      $node->arrexpires['month'],
      $node->arrexpires['day'],
      $node->arrexpires['year']
    );

  $sid = _guifi_db_sql(
    'supplier_quote',
    array('id' => $node->nid),
    (array)$node,
    $log,$to_mail);

  if ($node->deleted)
    $action = t('DELETED');
  else if ($node->new)
    $action = t('CREATED');
  else
    $action = t('UPDATED');

  $subject = t('The supplier quote %title has been %action by %user.',
    array('%title' => $node->title,
      '%action' => $action,
      '%user' => $user->name));

  drupal_set_message($subject);

  guifi_notify(
    $to_mail,
    $subject,
    $log);
}

function budgets_quote_insert($node) {
  $node->new = TRUE;
  $node->id = $node->nid;
  budgets_quote_save($node);
}

function budgets_quote_delete($node) {
  $node->deleted = TRUE;
  budgets_quote_save($node);
  drupal_goto('node/'.$node->supplier_id.'/quotes');
}

function budgets_quote_update($node) {
  budgets_quote_save($node);
}

function budgets_quote_load($node) {
  if (is_object($node))
    $k = $node->nid;
  else
    $k = $node;

  $node = db_fetch_object(
    db_query("SELECT * FROM {supplier_quote} WHERE id = '%d'", $k));

  if (is_null($node->id))
    return FALSE;

  return $node;
}

function budgets_quote_load_partno($partno) {
  $node = db_fetch_object(
    db_query("SELECT id FROM {supplier_quote} WHERE partno = '%s'", $partno));

  if (is_null($node->id))
    return FALSE;

  return node_load($node->id);
}

function budgets_quote_view($node, $teaser = FALSE, $page = FALSE) {
  $node = node_prepare($node, $teaser);
  $supplier = node_load(array('nid' => $node->supplier_id));

  $node->content['refquote'] = array(
    '#value'=> '<small>'.theme_refquote($node,$supplier,$teaser).'</small><hr>',
    '#weight' => -1,
  );

  return $node;
}

function budgets_quote_list_by_supplier_filter($parm,$supplier,$keyword) {
   guifi_log(GUIFILOG_TRACE,'form_filter',$supplier);

  /*
   * Filter form
   */
  $form['filter'] = array(
    '#title'=> t('Filter').' '.$keyword,
    '#type'=>'fieldset',
    '#tree'=>false,
    '#collapsed'=>true,
    '#collapsible'=>true,
  );
  $form['filter']['keyword'] = array(
    '#type'=>'textfield',
    '#size'=>60,
    '#title'=>t('Keyword'),
    '#description'=>t('Title contains (use single keyword)'),
    '#prefix'=> '<table><td>',
  );
  $form['filter']['supplier'] = array('#type'=>hidden,'#value'=>$supplier);
  $form['filter']['submit'] = array(
    '#type'=>'submit',
    '#title'=>t('Press to proceed'),
    '#value'=>t('Search'),
    '#suffix'=> '</td></table>',
  );

  return $form;
}

function budgets_quote_list_by_supplier_filter_submit($form_id, &$form_values) {
   $sup = $form_values['values'];
   guifi_log(GUIFILOG_TRACE,'SUBMIT',$sup);
   drupal_goto('node/'.$form_values['values']['supplier'].'/quotes/'.$form_values['values']['keyword']);
  }

function budgets_quote_list_by_supplier($supplier,$params=NULL) {

   guifi_log(GUIFILOG_TRACE,'quote_list (params)',$params);

   $output = drupal_get_form('budgets_quote_list_by_supplier_filter',$supplier->id,$params);

   if (!empty($params))
     $where = " AND upper(title) LIKE '%".strtoupper($params)."%' ";
   else
     $where = ' ';

   $qsql =  'SELECT id ' .
    'FROM {supplier_quote} ' .
    'WHERE supplier_id = '.$supplier->id.$where.
    'ORDER BY title, partno, id';


   $qquotes = pager_query(
    $qsql,
    variable_get('default_nodes_main', 10)
  );

  if (!$teaser) {
    $output .= '<br<br><hr><h2>'.t('Quotes from').': <em>'.$supplier->title.'</em></h2>';
    $q=0;
    while ($quote = db_fetch_object($qquotes)) {
      guifi_log(GUIFILOG_TRACE,'quote_list (supplier)',$quote);
      $output .= node_view(node_load(array('nid' => $quote->id)), TRUE, FALSE);
      $q++;
    }
    ($q==0) ? $output .= t('No quotes available') : NULL;

    $node->content['quotes'] = array(
      '#value'=> $output.
         theme('pager', NULL, variable_get('default_nodes_main', 10)),
      '#weight' => 1,
    );
  }

  $output .= theme('pager', NULL, variable_get('default_nodes_main', 10));

  //drupal_set_breadcrumb(guifi_zone_ariadna($zone->id,'node/%d/view/suppliers'));
  print theme('page',$output, FALSE);

  return;

  // antic

  $zroot = guifi_bg_zone_root();
  if (($zone->id==0) or (empty($zone->id))) {
    $zone->id=$zroot;
  }

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
      $where .= "OR CONCAT(','||s.zones||',') LIKE '%,".$z."%,' ";
    $where.=') ';
  }

  $qquery =
    'SELECT s.id ' .
    'FROM {supplier} s, {node} n ' .
    'WHERE s.id=n.nid ' .
    ' AND n.status=1 '.
    $where.
    'ORDER BY s.official_rating,role ';
  guifi_log(GUIFILOG_TRACE,'list_by_zone (suppliers query)',$qquery);

  $pager = pager_query($qquery,
    variable_get('default_nodes_main', 10)
  );
  $output = '';
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

?>

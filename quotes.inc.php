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

function budgets_quote_form(&$node) {
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


  $form['partno'] = array(
    '#type' => 'textfield',
    '#required' => TRUE,
    '#size' => 60,
    '#maxlength' => 60,
    '#title' => t('Part number'),
    '#description' => t('Part number/Code to identify this quote.'),
    '#default_value' => $node->partno,
  );

  $form['cost'] = array(
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
  $form['arrexpires'] = array(
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

function budgets_quote_ahah_select_supplier($string){
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

function budgets_quote_validate($node, &$form) {

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
  $node->delete = TRUE;
  budgets_quote_save($node);
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

function budgets_quote_view($node, $teaser = FALSE, $page = FALSE) {
  $node = node_prepare($node, $teaser);
  $supplier = node_load(array('nid' => $node->supplier_id));

  $node->content['refquote'] = array(
    '#value'=> '<small>'.theme_refquote($node,$supplier,$teaser).'</small><hr>',
    '#weight' => -1,
  );

  return $node;
}

?>

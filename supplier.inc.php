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

function budgets_supplier_form(&$node) {
  $type = node_get_types('type',$node);

  if (($type->has_title)) {
    $form['title'] = array(
      '#type' => 'textfield',
      '#title' => check_plain($type->title_label),
      '#required' => TRUE,
      '#default_value' => $node->title,
    );
  }

  $form['zone_id'] = guifi_zone_select_field($node->zone_id,'zone_id');

  if (($type->has_body)) {
    $form['body_field'] = node_body_field(
      $node,
      $type->body_label,
      $type->min_word_count
    );
  }

  return $form;
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

function budgets_supplier_save($node) {
  global $user;

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
  $node->delete = TRUE;
  budgets_supplier_save($node);
}

function budgets_supplier_update($node) {
  budgets_supplier_save($node);
}

function budgets_supplier_load($node) {
  if (is_object($node))
    $k = $node->nid;
  else
    $k = $node;

  $node = db_fetch_object(
    db_query("SELECT * FROM {supplier} WHERE id = '%d'", $k));

  if (is_null($node->id))
    return FALSE;

  return $node;
}

function budgets_supplier_list_by_zone($zone) {
  $parents = guifi_zone_get_parents($zone->id);

  $pager = pager_query(
    'SELECT id ' .
    'FROM {supplier} ' .
    'WHERE zone_id IN ('.implode(',',$parents).')',
    variable_get('default_nodes_main', 10)
  );
  $output = '';
  while ($s = db_fetch_object($pager)) {
    $supplier = node_load(array('nid' => $s->id));
    $output .= node_view($supplier, TRUE, FALSE, TRUE);
  }
  $output .= theme('pager', NULL, variable_get('default_nodes_main', 10));

  drupal_set_breadcrumb(guifi_zone_ariadna($zone->id,'node/%d/view/suppliers'));
  $output .= theme_pager(NULL, variable_get("guifi_pagelimit", 50));
  $node = node_load(array('nid' => $zone->id));
  $output .= theme_links(module_invoke_all('link', 'node', $node, FALSE));
  print theme('page',$output, FALSE);
  return;
}

function budgets_supplier_view($node, $teaser = FALSE, $page = FALSE) {
  $node = node_prepare($node, $teaser);

  $qquotes = pager_query(
    sprintf('SELECT id ' .
    'FROM {supplier_quote} ' .
    'WHERE supplier_id = %d ' .
    'ORDER BY title, partno, id',
    $node->nid),
    variable_get('default_nodes_main', 10)
  );

  if (!$teaser) {
    $output = '<h2>'.t('Quotes').'</h2>';
    while ($quote = db_fetch_object($qquotes)) {;
      $output .= node_view(node_load(array('nid' => $quote->id)), TRUE, FALSE);
    }

    $node->content['quotes'] = array(
      '#value'=> $output.
         theme('pager', NULL, variable_get('default_nodes_main', 10)),
      '#weight' => 1,
    );
  }

  return $node;
}



?>

SELECT 
  b.id 'Key',            /* Key to join with items */
  b.summary Title,       /* Title */ 
  concat(replace(replace(bt.description,'\n','<br>'),'\r','')) body,
  from_unixtime(b.date_submitted,'%Y-%m-%d') 'Date', /* Due Date in YYYY-MM-DD format */
  '' Node,           /* Affected guifi.net node */
  '' Budget_Type,    /* Types: others,none-crowdfunding,w-capex,w-opex,f-capex,f-opex,f-conn */
  '' Priority,       /* Priority: unknown,urgent,normal,minor */
  '' Ticket  /* URL of the related ticket */
FROM mantis_bug_table b, mantis_bug_text_table bt
WHERE b.project_id=19
AND b.bug_text_id = bt.id;

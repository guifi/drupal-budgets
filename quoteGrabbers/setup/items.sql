SELECT 
  b.id 'Key',
  concat(' *** ',from_unixtime(bn.date_submitted),' *** ') Title, /* Using date submitted as a item title */
    replace(replace(bnt.note,'\n','<br>'),'\r','') Description,
  '' Comments,  /* No comments, so NULL value */ 
  bn.time_tracking/60 Quantity, /* Given in minutes, so have to convert to hours */
  30 Cost, 	/* Labour cost fixed to 30€ */
  21 Tax 	/* VAT: Fixed to 21 */
FROM mantis_bug_table b,mantis_bugnote_table bn, mantis_bugnote_text_table bnt
WHERE b.project_id=19
AND b.id = bn.bug_id
AND bnt.id = bn.bugnote_text_id
ORDER BY b.id, bn.date_submitted;

select *
from container co, restore_file_co2 rfc2, restore_files rf
where rf.id = rfc2.rf_code
and rfc2.co_code = co.i_autocode
and s_reference like '20%';

select *
from container
where s_reference like 'USA_NY%';


select *
from container
where i_autocode = 56916;

update restore_files2 rf
  inner join  restore_file_co2 rfc  on rf_code = id
    set rf.to_restore = 1
    where rfc.is_restored = 0
            and to_restore = 0

and co_code not in (select co_code from restore_file_co)
;

select *
  from restore_files2 rf
  inner join  restore_file_co2 rfc  on rf_code = id
        where rfc.is_restored = 0
            and to_restore = 0
and co_code not in (select co_code from restore_file_co);

select *
from restore_files2
where id in (1557,1702,1703);

show PROCESSLIST ;


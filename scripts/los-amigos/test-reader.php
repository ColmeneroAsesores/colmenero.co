<?php
require __DIR__.'/../../los-amigos/control/lib/sheets.php';
function expect($condition,$message) {if (!$condition) throw new RuntimeException($message);}
$rh=['ID Reserva','Nombre','Entrada','Salida','Adultos','Niños','Estado estancia'];
$ah=['ID Reserva','Alojamiento','Entrada','Salida','Estado'];
$r=[$rh,['DEMO1','Persona ficticia A','2026-09-01','2026-09-03',2,0,'Confirmada'],['DEMO2','Persona ficticia B','2026-09-03','2026-09-05',4,0,'Confirmada'],['DEMO3','Grupo ficticio','2026-09-01','2026-09-05',18,0,'Confirmada'],['DEMO4','Cancelado ficticio','2026-09-01','2026-09-05',2,0,'Cancelada']];
$a=[$ah,['DEMO1','Mariposa','2026-09-01','2026-09-03','Activa'],['DEMO2','Mariposa','2026-09-03','2026-09-05','Activa'],['DEMO3','Chango','2026-09-01','2026-09-05','Activa'],['DEMO3','Guacamaya','2026-09-01','2026-09-05','Activa'],['DEMO4','Nido','2026-09-01','2026-09-05','Activa']];
$x=la_stays($r,$a,'2026-09-03');expect(count($x)===4,'checkout plus arrival and distinct group units');
$m=array_values(array_filter($x,fn($x)=>$x['cabin']==='Mariposa'));expect(count($m)===2,'turnover preserved');expect($m[0]['people']===2 && $m[1]['people']===4,'single unit count');
expect($x[0]['people']===null && $x[1]['people']===null,'do not distribute group totals');
expect(la_stays($r,$a,'2026-09-06')===[],'empty date');
expect(la_day('31/2/2026')===null,'invalid calendar date');expect(la_day(46267)==='2026-09-02','Sheets serial');
$r[1][4]='';expect(la_stays($r,$a,'2026-09-02')[2]['people']===null,'blank is not zero');
$a[1][4]='Liberada';expect(count(la_stays($r,$a,'2026-09-02'))===2,'released excluded');
try {la_stays([['bad']],$a,'2026-09-01');throw new Exception('schema must fail');} catch(RuntimeException $e) {expect($e->getMessage()==='schema','fail closed');}
foreach($x as $stay) expect(array_keys($stay)===['cabin','name','people','arrival','departure'],'only allowed data');
echo "PASS reader: dates, checkout turnover, cancellations, group counts, unknown counts, schema and output minimization\n";

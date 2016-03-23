# racktables-plugins-draw-racks
Outputs the rack layout to the MS Excel sheets

RackTables で作成したラック配置図をExcelに出力するプラグインです。

動作させるためには PHP Excel(https://phpexcel.codeplex.com/)が必要です。

インストール方法
ファイルをpluginsディレクトリ以下に配置します。

＋plugins
    drawracks.php
   +drawracks
      drawracksConfig.php   -- Configuration File(If necessary)
      drawracksLib.php
     +xlsx             -- Template File
       drawracks.xlsx
     +Classes          -- PHP Excel
       +PHPExcel
        PHPExcel.php

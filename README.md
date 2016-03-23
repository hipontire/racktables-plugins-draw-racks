# racktables-plugins-draw-racks
Outputs the rack layout to the MS Excel sheets

<h3>RackTables で作成したラック配置図をExcelに出力するプラグインです。</h3>

動作させるためには <A href="https://phpexcel.codeplex.com/" title="php Excel">PHP Excel</a>が必要です。

<h2>インストール方法</h2>
ファイルをpluginsディレクトリ以下に配置します。
<pre>
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
</pre>

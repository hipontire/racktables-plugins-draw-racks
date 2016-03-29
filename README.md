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

<h2>使用方法</h2>
Reports &gt; DrawRacks タブから出力するラックを選択し、「Export rack layout」をクリックします。

<h2>出力サンプル</h2>
<a href="https://raw.githubusercontent.com/hipontire/racktables-plugins-draw-racks/master/snapshot.png" target="_blank"><img src="https://github.com/hipontire/racktables-plugins-draw-racks/blob/master/snapshot.png" width="589" height="419"></a>


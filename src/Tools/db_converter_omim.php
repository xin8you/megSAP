<?php

require_once(dirname($_SERVER['SCRIPT_FILENAME'])."/../Common/all.php");

//init
$ngsbits = get_path("ngs-bits");
$skipped = array(
	"approved gene missing" => array(),
	"approved gene name unknown" => array(),
	"approved gene not convertable to genomic coordinates" => array(),
	);

//parse "genemap2.txt" for genes and disorders
/*
# Chromosome	Genomic Position Start	Genomic Position End	Cyto Location	Computed Cyto Location	Mim Number	Gene Symbols	Gene Name	Approved Symbol	Entrez Gene ID	Ensembl Gene ID	Comments	Phenotypes	Mouse Gene Symbol/ID
chr1	975197	982116	1p36.33	1p36.33	615921	PERM1, C1orf170	PPARGC1-and ESRR-induced regulator, muscle, 1	PERM1	84808	ENSG00000187642			
chr1	998961	1001051	1p36.31	1p36.33	608060	HES4	Hairy/enhancer of split, Drosophila, homolog of, 4	HES4	57801	ENSG00000188290			
*/
$handle = fopen2("genemap2.txt", "r");
print "#chr	start	end	omim\n";
while(!feof($handle))
{
	$line = nl_trim(fgets($handle));
	if($line=="" || $line[0]=="#") continue;
	
	$parts = explode("\t", $line);
	if (count($parts)<14)
	{
		print_r($parts);
		trigger_error("Line '$line' does not contain 14 tab-separated parts!", E_USER_ERROR);
	}
	
	//id
	$mim_id = trim($parts[5]);
	
	//disorders not empty
	$disorders = trim($parts[12]);
	$disorders = trim(strtr($disorders, array("(1)"=>"", "(2)"=>"", "(3)"=>"", "(4)"=>"", "["=>"", "]"=>"", "{"=>"", "}"=>"", "?"=>"", ";"=>"|")));
	$disorders = trim(strtr($disorders, array(" , "=>",", " ,"=>",", ", "=>",")));
	$disorders = trim(strtr($disorders, array(" | "=>"/", " |"=>"/", "| "=>"/")));
	$disorders = trim(strtr($disorders, array(" "=>"_")));
	if ($disorders=="") continue;
	
	//chromosome
	$chr_omim = trim($parts[0]);
	
	//extract gene
	$gene = trim($parts[8]);
	if ($gene=="")
	{
		$skipped["approved gene missing"][] = $mim_id;
		continue;
	}
	
	//convert gene name to approved symbol
	list($stdout) = exec2("echo '{$gene}' | {$ngsbits}GenesToApproved");
	list($gene_approved, $message) = explode("\t", trim(implode(" ", $stdout)));
	if (contains($message, "ERROR"))
	{
		$skipped["approved gene name unknown"][] = $mim_id."/".$gene;
		continue;
	}
	
	//only genes with coorinates on right chromosome
	list($stdout, $stderr, $exit_code) = exec2("echo '{$gene_approved}' | {$ngsbits}GenesToBed -source ensembl -mode gene -fallback | egrep '{$chr_omim}\s' | {$ngsbits}BedExtend -n 20 | {$ngsbits}BedMerge", false);
	if ($exit_code!=0 && trim(implode("", $stdout))=="")
	{
		$skipped["approved gene not convertable to genomic coordinates"][] = $mim_id."/".$gene_approved."/".$chr_omim;
		continue;
	}
	
	//output
	foreach($stdout as $line)
	{
		$line = trim($line);
		if ($line=="") continue;
		list($chr, $start, $end) = explode("\t", trim($line));
		print "$chr	$start	$end	{$mim_id}_[GENE%3D{$gene_approved}_PHENOS%3D{$disorders}]\n";
	}	
}
fclose($handle);

print "##MIM entries skipped because:\n";
foreach($skipped as $reason => $entries)
{
	print "##  {$reason}: ".count($entries)."\n";
}

?>
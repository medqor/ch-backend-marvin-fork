<?php
error_reporting(E_ALL);
ini_set('display_errors', true);
ignore_user_abort();

use Aws\S3\S3Client;
use Aws\S3\Model\ClearBucket;

set_time_limit(0);

class nppes_etl extends service
{
    public $report;
    public $maxFileSize = 5000;
    public $errors = 0;
    public $totalFileLines = 0;
    public $year = 2017;
    public $showCSVCreate = true;
    public $file_url;
    public $dateString;
    public $outFileCount = 0;
    public $outFile = false;
    public $lastCount=0;
    public $columns = ["NPI",
        "Entity Type Code",
        "Replacement NPI",
        "Employer Identification Number (EIN)",
        "Provider Organization Name (Legal Business Name)",
        "Provider Last Name (Legal Name)",
        "Provider First Name",
        "Provider Middle Name",
        "Provider Name Prefix Text",
        "Provider Name Suffix Text",
        "Provider Credential Text",
        "Provider Other Organization Name",
        "Provider Other Organization Name Type Code",
        "Provider Other Last Name",
        "Provider Other First Name",
        "Provider Other Middle Name",
        "Provider Other Name Prefix Text",
        "Provider Other Name Suffix Text",
        "Provider Other Credential Text",
        "Provider Other Last Name Type Code",
        "Provider First Line Business Mailing Address",
        "Provider Second Line Business Mailing Address",
        "Provider Business Mailing Address City Name",
        "Provider Business Mailing Address State Name",
        "Provider Business Mailing Address Postal Code",
        "Provider Business Mailing Address Country Code (If outside U.S.)",
        "Provider Business Mailing Address Telephone Number",
        "Provider Business Mailing Address Fax Number",
        "Provider First Line Business Practice Location Address",
        "Provider Second Line Business Practice Location Address",
        "Provider Business Practice Location Address City Name",
        "Provider Business Practice Location Address State Name",
        "Provider Business Practice Location Address Postal Code",
        "Provider Business Practice Location Address Country Code (If outside U.S.)",
        "Provider Business Practice Location Address Telephone Number",
        "Provider Business Practice Location Address Fax Number",
        "Provider Enumeration Date",
        "Last Update Date",
        "NPI Deactivation Reason Code",
        "NPI Deactivation Date",
        "NPI Reactivation Date",
        "Provider Gender Code",
        "Authorized Official Last Name",
        "Authorized Official First Name",
        "Authorized Official Middle Name",
        "Authorized Official Title or Position",
        "Authorized Official Telephone Number",
        "Healthcare Provider Taxonomy Code_1",
        "Provider License Number_1",
        "Provider License Number State Code_1",
        "Healthcare Provider Primary Taxonomy Switch_1",
        "Healthcare Provider Taxonomy Code_2",
        "Provider License Number_2",
        "Provider License Number State Code_2",
        "Healthcare Provider Primary Taxonomy Switch_2",
        "Healthcare Provider Taxonomy Code_3",
        "Provider License Number_3",
        "Provider License Number State Code_3",
        "Healthcare Provider Primary Taxonomy Switch_3",
        "Healthcare Provider Taxonomy Code_4",
        "Provider License Number_4",
        "Provider License Number State Code_4",
        "Healthcare Provider Primary Taxonomy Switch_4",
        "Healthcare Provider Taxonomy Code_5",
        "Provider License Number_5",
        "Provider License Number State Code_5",
        "Healthcare Provider Primary Taxonomy Switch_5",
        "Healthcare Provider Taxonomy Code_6",
        "Provider License Number_6",
        "Provider License Number State Code_6",
        "Healthcare Provider Primary Taxonomy Switch_6",
        "Healthcare Provider Taxonomy Code_7",
        "Provider License Number_7",
        "Provider License Number State Code_7",
        "Healthcare Provider Primary Taxonomy Switch_7",
        "Healthcare Provider Taxonomy Code_8",
        "Provider License Number_8",
        "Provider License Number State Code_8",
        "Healthcare Provider Primary Taxonomy Switch_8",
        "Healthcare Provider Taxonomy Code_9",
        "Provider License Number_9",
        "Provider License Number State Code_9",
        "Healthcare Provider Primary Taxonomy Switch_9",
        "Healthcare Provider Taxonomy Code_10",
        "Provider License Number_10",
        "Provider License Number State Code_10",
        "Healthcare Provider Primary Taxonomy Switch_10",
        "Healthcare Provider Taxonomy Code_11",
        "Provider License Number_11",
        "Provider License Number State Code_11",
        "Healthcare Provider Primary Taxonomy Switch_11",
        "Healthcare Provider Taxonomy Code_12",
        "Provider License Number_12",
        "Provider License Number State Code_12",
        "Healthcare Provider Primary Taxonomy Switch_12",
        "Healthcare Provider Taxonomy Code_13",
        "Provider License Number_13",
        "Provider License Number State Code_13",
        "Healthcare Provider Primary Taxonomy Switch_13",
        "Healthcare Provider Taxonomy Code_14",
        "Provider License Number_14",
        "Provider License Number State Code_14",
        "Healthcare Provider Primary Taxonomy Switch_14",
        "Healthcare Provider Taxonomy Code_15",
        "Provider License Number_15",
        "Provider License Number State Code_15",
        "Healthcare Provider Primary Taxonomy Switch_15",
        "Other Provider Identifier_1",
        "Other Provider Identifier Type Code_1",
        "Other Provider Identifier State_1",
        "Other Provider Identifier Issuer_1",
        "Other Provider Identifier_2",
        "Other Provider Identifier Type Code_2",
        "Other Provider Identifier State_2",
        "Other Provider Identifier Issuer_2",
        "Other Provider Identifier_3",
        "Other Provider Identifier Type Code_3",
        "Other Provider Identifier State_3",
        "Other Provider Identifier Issuer_3",
        "Other Provider Identifier_4",
        "Other Provider Identifier Type Code_4",
        "Other Provider Identifier State_4",
        "Other Provider Identifier Issuer_4",
        "Other Provider Identifier_5",
        "Other Provider Identifier Type Code_5",
        "Other Provider Identifier State_5",
        "Other Provider Identifier Issuer_5",
        "Other Provider Identifier_6",
        "Other Provider Identifier Type Code_6",
        "Other Provider Identifier State_6",
        "Other Provider Identifier Issuer_6",
        "Other Provider Identifier_7",
        "Other Provider Identifier Type Code_7",
        "Other Provider Identifier State_7",
        "Other Provider Identifier Issuer_7",
        "Other Provider Identifier_8",
        "Other Provider Identifier Type Code_8",
        "Other Provider Identifier State_8",
        "Other Provider Identifier Issuer_8",
        "Other Provider Identifier_9",
        "Other Provider Identifier Type Code_9",
        "Other Provider Identifier State_9",
        "Other Provider Identifier Issuer_9",
        "Other Provider Identifier_10",
        "Other Provider Identifier Type Code_10",
        "Other Provider Identifier State_10",
        "Other Provider Identifier Issuer_10",
        "Other Provider Identifier_11",
        "Other Provider Identifier Type Code_11",
        "Other Provider Identifier State_11",
        "Other Provider Identifier Issuer_11",
        "Other Provider Identifier_12",
        "Other Provider Identifier Type Code_12",
        "Other Provider Identifier State_12",
        "Other Provider Identifier Issuer_12",
        "Other Provider Identifier_13",
        "Other Provider Identifier Type Code_13",
        "Other Provider Identifier State_13",
        "Other Provider Identifier Issuer_13",
        "Other Provider Identifier_14",
        "Other Provider Identifier Type Code_14",
        "Other Provider Identifier State_14",
        "Other Provider Identifier Issuer_14",
        "Other Provider Identifier_15",
        "Other Provider Identifier Type Code_15",
        "Other Provider Identifier State_15",
        "Other Provider Identifier Issuer_15",
        "Other Provider Identifier_16",
        "Other Provider Identifier Type Code_16",
        "Other Provider Identifier State_16",
        "Other Provider Identifier Issuer_16",
        "Other Provider Identifier_17",
        "Other Provider Identifier Type Code_17",
        "Other Provider Identifier State_17",
        "Other Provider Identifier Issuer_17",
        "Other Provider Identifier_18",
        "Other Provider Identifier Type Code_18",
        "Other Provider Identifier State_18",
        "Other Provider Identifier Issuer_18",
        "Other Provider Identifier_19",
        "Other Provider Identifier Type Code_19",
        "Other Provider Identifier State_19",
        "Other Provider Identifier Issuer_19",
        "Other Provider Identifier_20",
        "Other Provider Identifier Type Code_20",
        "Other Provider Identifier State_20",
        "Other Provider Identifier Issuer_20",
        "Other Provider Identifier_21",
        "Other Provider Identifier Type Code_21",
        "Other Provider Identifier State_21",
        "Other Provider Identifier Issuer_21",
        "Other Provider Identifier_22",
        "Other Provider Identifier Type Code_22",
        "Other Provider Identifier State_22",
        "Other Provider Identifier Issuer_22",
        "Other Provider Identifier_23",
        "Other Provider Identifier Type Code_23",
        "Other Provider Identifier State_23",
        "Other Provider Identifier Issuer_23",
        "Other Provider Identifier_24",
        "Other Provider Identifier Type Code_24",
        "Other Provider Identifier State_24",
        "Other Provider Identifier Issuer_24",
        "Other Provider Identifier_25",
        "Other Provider Identifier Type Code_25",
        "Other Provider Identifier State_25",
        "Other Provider Identifier Issuer_25",
        "Other Provider Identifier_26",
        "Other Provider Identifier Type Code_26",
        "Other Provider Identifier State_26",
        "Other Provider Identifier Issuer_26",
        "Other Provider Identifier_27",
        "Other Provider Identifier Type Code_27",
        "Other Provider Identifier State_27",
        "Other Provider Identifier Issuer_27",
        "Other Provider Identifier_28",
        "Other Provider Identifier Type Code_28",
        "Other Provider Identifier State_28",
        "Other Provider Identifier Issuer_28",
        "Other Provider Identifier_29",
        "Other Provider Identifier Type Code_29",
        "Other Provider Identifier State_29",
        "Other Provider Identifier Issuer_29",
        "Other Provider Identifier_30",
        "Other Provider Identifier Type Code_30",
        "Other Provider Identifier State_30",
        "Other Provider Identifier Issuer_30",
        "Other Provider Identifier_31",
        "Other Provider Identifier Type Code_31",
        "Other Provider Identifier State_31",
        "Other Provider Identifier Issuer_31",
        "Other Provider Identifier_32",
        "Other Provider Identifier Type Code_32",
        "Other Provider Identifier State_32",
        "Other Provider Identifier Issuer_32",
        "Other Provider Identifier_33",
        "Other Provider Identifier Type Code_33",
        "Other Provider Identifier State_33",
        "Other Provider Identifier Issuer_33",
        "Other Provider Identifier_34",
        "Other Provider Identifier Type Code_34",
        "Other Provider Identifier State_34",
        "Other Provider Identifier Issuer_34",
        "Other Provider Identifier_35",
        "Other Provider Identifier Type Code_35",
        "Other Provider Identifier State_35",
        "Other Provider Identifier Issuer_35",
        "Other Provider Identifier_36",
        "Other Provider Identifier Type Code_36",
        "Other Provider Identifier State_36",
        "Other Provider Identifier Issuer_36",
        "Other Provider Identifier_37",
        "Other Provider Identifier Type Code_37",
        "Other Provider Identifier State_37",
        "Other Provider Identifier Issuer_37",
        "Other Provider Identifier_38",
        "Other Provider Identifier Type Code_38",
        "Other Provider Identifier State_38",
        "Other Provider Identifier Issuer_38",
        "Other Provider Identifier_39",
        "Other Provider Identifier Type Code_39",
        "Other Provider Identifier State_39",
        "Other Provider Identifier Issuer_39",
        "Other Provider Identifier_40",
        "Other Provider Identifier Type Code_40",
        "Other Provider Identifier State_40",
        "Other Provider Identifier Issuer_40",
        "Other Provider Identifier_41",
        "Other Provider Identifier Type Code_41",
        "Other Provider Identifier State_41",
        "Other Provider Identifier Issuer_41",
        "Other Provider Identifier_42",
        "Other Provider Identifier Type Code_42",
        "Other Provider Identifier State_42",
        "Other Provider Identifier Issuer_42",
        "Other Provider Identifier_43",
        "Other Provider Identifier Type Code_43",
        "Other Provider Identifier State_43",
        "Other Provider Identifier Issuer_43",
        "Other Provider Identifier_44",
        "Other Provider Identifier Type Code_44",
        "Other Provider Identifier State_44",
        "Other Provider Identifier Issuer_44",
        "Other Provider Identifier_45",
        "Other Provider Identifier Type Code_45",
        "Other Provider Identifier State_45",
        "Other Provider Identifier Issuer_45",
        "Other Provider Identifier_46",
        "Other Provider Identifier Type Code_46",
        "Other Provider Identifier State_46",
        "Other Provider Identifier Issuer_46",
        "Other Provider Identifier_47",
        "Other Provider Identifier Type Code_47",
        "Other Provider Identifier State_47",
        "Other Provider Identifier Issuer_47",
        "Other Provider Identifier_48",
        "Other Provider Identifier Type Code_48",
        "Other Provider Identifier State_48",
        "Other Provider Identifier Issuer_48",
        "Other Provider Identifier_49",
        "Other Provider Identifier Type Code_49",
        "Other Provider Identifier State_49",
        "Other Provider Identifier Issuer_49",
        "Other Provider Identifier_50",
        "Other Provider Identifier Type Code_50",
        "Other Provider Identifier State_50",
        "Other Provider Identifier Issuer_50",
        "Is Sole Proprietor",
        "Is Organization Subpart",
        "Parent Organization LBN",
        "Parent Organization TIN",
        "Authorized Official Name Prefix Text",
        "Authorized Official Name Suffix Text",
        "Authorized Official Credential Text",
        "Healthcare Provider Taxonomy Group_1",
        "Healthcare Provider Taxonomy Group_2",
        "Healthcare Provider Taxonomy Group_3",
        "Healthcare Provider Taxonomy Group_4",
        "Healthcare Provider Taxonomy Group_5",
        "Healthcare Provider Taxonomy Group_6",
        "Healthcare Provider Taxonomy Group_7",
        "Healthcare Provider Taxonomy Group_8",
        "Healthcare Provider Taxonomy Group_9",
        "Healthcare Provider Taxonomy Group_10",
        "Healthcare Provider Taxonomy Group_11",
        "Healthcare Provider Taxonomy Group_12",
        "Healthcare Provider Taxonomy Group_13",
        "Healthcare Provider Taxonomy Group_14",
        "Healthcare Provider Taxonomy Group_15",
        'Certification Date'];

    public function __construct()
    {
        if(isset($_REQUEST['offset'])){
            $this->lastCount=$_REQUEST['offset'];
            $this->upsertFirstBatch=true;
            $_SESSION['offset']=$this->lastCount;
        }else{
            $this->upsertFirstBatch = false;
        }
        $this->session = date("YmdH");
        $this->import_folder = $_SERVER['DOCUMENT_ROOT'] . '/../import/';
        $this->allowed_actions = array('view', 'import','load');

        $this->_preprocess();
        $this->registry->addValue('system_error', 'Hi');
        $this->_format = 'json';
        if (date("d") < 15) {
            $this->dateString = date("F_Y", strtotime('-1 month'));
        } else {
            $this->dateString = date("F_Y", strtotime('today'));
        }

    }

    protected function load(){
        $this->outfileFileFilter = sprintf( '/import/*.json');

        $readyImport = glob($this->outfileFileFilter);


        foreach($readyImport as $idx => $file){
            $status = $this->mongoimport('base', 'national_provider', $file, 'json', 'upsert', true,'npi');
            if ($status === true) {
                live_flush("successfully upserted " . $file, 'upsert');
                unlink($file);
                $cmd=sprintf('find . -name %s -exec  mongoimport --ssl --host chroniclehealth-primary.cluster-cdqcr83pkeam.us-west-2.docdb.amazonaws.com:27017 --sslCAFile /home/ubuntu/rds-combined-ca-bundle.pem --username root --password SkJxY9ubJTNF --db=base --type=json --mode=insert   --collection=national_provider_identifier --numInsertionWorkers=8 --jsonArray --file=\'{}\' \;',  $this->outfileFileFilter );
                live_append($cmd, 'command');
            } else {
                live_flush("falied to upsert " .$file, 'upsert');
            }
        }


    }

    /**
     * Main NPPES data file. breaks down into 4 tables
     */
    protected function import()
    {
        ob_start();
        $this->_format = 'live';
        $this->start = strtotime('now');
        $this->etldate = date("Y-m-d H:i:s");
        // $this->etldate = date("Y-m-d H:i:s",strtotime('2019-08-23 07:49:31'));
        live_template('nppes_etl/nppes');
        ob_flush();
        ob_end_flush();
        ob_clean();
        // $this->logETL('import_nppes','import',0,-1,strtotime('now'));

        if (!isset($_REQUEST['skip_import'])) {

            #$this->completed=['npi__','nppes_healthcare_provider__','nppes_healthcare_provider_taxonomy_group__','nppes_other_provider_identifier__'];
            $this->completed = [];


            live_flush($this->dateString, 'for');


            switch ($this->check_updated_file($this->dateString)) {
                case 1:
                    live_flush("Eligible File  Found for $this->dateString: ", 'file');
                    break;
                case 0:
                    live_flush("Eligible File  NOT found for $this->dateString: ", 'file');
                    break;
                case -1:
                    live_flush("Latest file already loaded: ", 'file');
                    break;
            }


            $url = sprintf('http://download.cms.gov/nppes/NPPES_Data_Dissemination_%s.zip', $this->dateString);
            $this->file_url = $url;


            $file_base = 'npidata_pfile.zip';
            $this->file_base = $this->downloadLarge($url, $file_base);

            $this->unzip_path = $this->unzip($this->getImportFilePath($file_base), false);

            $extractedFilePath = $this->unzip_path . '*';
            live_flush("Checking $extractedFilePath for files");
            $files = glob($extractedFilePath);

            foreach ($files as $file) {
                if (strstr($file, 'npidata_pfile') && !strstr($file, 'FileHeader')) {
                    $npi_source_file = $file;
                    live_flush("Found $file");
                }
            }


            $columns = $this->columns;
            $cmd = sprintf("wc -l < %s", $npi_source_file);

            $this->totalFileLines = number_format(trim(shell_exec($cmd)), 0, '.', '');
            sleep(1);
            live_flush(number_format($this->totalFileLines, 0), 'total');
            sleep(1);
            $source = fopen($npi_source_file, 'r+');
            $errorFile="/import/import.error";


            //remove the top row, it just has the column headers
            $row = fgetcsv($source);
            $ct = 0;
            $sql = "SELECT * from nppes_taxonomy_codes";
            $data = $this->query('_redshift', $sql);
            $taxonomy = [];
            foreach ($data as $row) {
                $code = $row['code'];
                unset($row['code']);
                unset($row['notes']);
                $taxonomy[$code] = $row;

            }
            unset($data);


            $ct = 0;
            $this->ct = -1;
            while (!feof($source)) {
                $this->ct++;
                $json = [];


                $row = fgetcsv($source);
                $row = $this->cleanPipes($row);
                if (!is_array($row)) {
                    continue;
                }
                if ($this->ct < $this->lastCount) {

                    continue;
                }

                if(count($columns) != count($row)){
                    $this->errors++;
                   $tfp=fopen($errorFile,'a+');
                   fputcsv($tfp,$row);
                   fclose($tfp);

                   live_flush(count($row)." columns found, skipping [{$this->errors}]",'errors');
                    continue;
                }else{
                    $row = array_combine($columns, $row);
                }



                if (!trim($row['Entity Type Code'])) {
                    continue;
                }
                $json['npi'] = $row['NPI'];
                $json['last_update_date'] = $row['Last Update Date'];
                $json['replacement_npi'] = $row['Replacement NPI'];
                $json['npi_deactivation_reason'] = $row['NPI Deactivation Reason Code'];
                $json['npi_deactivation_date'] = $row['NPI Deactivation Date'];
                $json['npi_reactivation_date'] = $row['NPI Reactivation Date'];
                $json['certification_date'] = $row['Certification Date'];
                $json['provider_enumeration_date'] = $row['Provider Enumeration Date'];
                if ($row['Entity Type Code'] == '1') {
                    $json['type'] = 'person';
                    $json['first_name'] = $row['Provider First Name'];
                    $json['middle_name'] = $row['Provider Middle Name'];
                    $json['last_name'] = $row['Provider Last Name (Legal Name)'];
                    $json['prefix'] = $row['Provider Name Prefix Text'];
                    $json['suffix'] = $row['Provider Name Suffix Text'];
                    $json['credentials'] = $row['Provider Credential Text'];
                    if (trim($row['Provider Other Last Name'])) {
                        $json['other_names'][] =
                            ['last_name' => $row['Provider Other Last Name'],
                                'first_name' => $row['Provider Other First Name'],
                                'middle_name' => $row['Provider Other Middle Name'],
                                'prefix' => $row['Provider Other Name Prefix Text'],
                                'suffix' => $row['Provider Other Name Suffix Text'],
                                'credentials' => $row['Provider Other Credential Text']];
                    }
                    $json['gender'] = $row['Provider Gender Code'];


                } else {
                    $json['type'] = 'practice';
                    $json['legal_business_name'] = $row['Provider Organization Name (Legal Business Name)'];
                    $json['other_organization_name'] = $row['Provider Other Organization Name'];
                    $json['other_organization_name_type'] = $row['Provider Other Organization Name Type Code'];

                    $json[''] = $row['Authorized Official Last Name'];
                    $json[''] = $row['Authorized Official First Name'];
                    $json[''] = $row['Authorized Official Middle Name'];
                    $json[''] = $row['Authorized Official Title or Position'];
                    $json[''] = $row['Authorized Official Telephone Number'];
                    $json[''] = $row['Authorized Official Name Prefix Text'];
                    $json[''] = $row['Authorized Official Name Suffix Text'];
                    $json[''] = $row['Authorized Official Credential Text'];
                    $json[''] = $row['Is Sole Proprietor'];
                    $json[''] = $row['Is Organization Subpart'];
                    $json[''] = $row['Parent Organization LBN'];
                    $json[''] = $row['Parent Organization TIN'];

                }

                unset($row['NPI']);
                unset($row['Entity Type Code']);
                unset($row['Replacement NPI']);
                unset($row['Employer Identification Number (EIN)']);
                unset($row['Provider Organization Name (Legal Business Name)']);
                unset($row['Provider Last Name (Legal Name)']);
                unset($row['Provider First Name']);
                unset($row['Provider Middle Name']);
                unset($row['Provider Name Prefix Text']);
                unset($row['Provider Name Suffix Text']);
                unset($row['Provider Credential Text']);
                unset($row['Provider Other Organization Name']);
                unset($row['Provider Other Organization Name Type Code']);
                unset($row['Provider Other Last Name']);
                unset($row['Provider Other First Name']);
                unset($row['Provider Other Middle Name']);
                unset($row['Provider Other Name Prefix Text']);
                unset($row['Provider Other Name Suffix Text']);
                unset($row['Provider Other Credential Text']);
                unset($row['Provider Other Last Name Type Code']);

                unset($row['Provider Enumeration Date']);
                unset($row['Last Update Date']);
                unset($row['NPI Deactivation Reason Code']);
                unset($row['NPI Deactivation Date']);
                unset($row['NPI Reactivation Date']);
                unset($row['Provider Gender Code']);
                unset($row['Authorized Official Last Name']);
                unset($row['Authorized Official First Name']);
                unset($row['Authorized Official Middle Name']);
                unset($row['Authorized Official Title or Position']);
                unset($row['Authorized Official Telephone Number']);
                unset($row['Is Sole Proprietor']);
                unset($row['Is Organization Subpart']);
                unset($row['Parent Organization LBN']);
                unset($row['Parent Organization TIN']);
                unset($row['Authorized Official Name Prefix Text']);
                unset($row['Authorized Official Name Suffix Text']);
                unset($row['Authorized Official Credential Text']);
                unset($row['Certification Date']);

                if (trim($row['Provider First Line Business Mailing Address'])) {
                    $json['addresses'][] =
                        [
                            'type' => 'mailing',
                            'first_line' => $row['Provider First Line Business Mailing Address'],
                            'second_line' => $row['Provider Second Line Business Mailing Address'],
                            'city' => $row['Provider Business Mailing Address City Name'],
                            'state' => $row['Provider Business Mailing Address State Name'],
                            'postal_code' => $row['Provider Business Mailing Address Postal Code'],
                            'country' => trim($row['Provider Business Mailing Address Country Code (If outside U.S.)']) ?? 'US',

                        ];
                }
                unset($row['Provider First Line Business Mailing Address']);
                unset($row['Provider Second Line Business Mailing Address']);
                unset($row['Provider Business Mailing Address City Name']);
                unset($row['Provider Business Mailing Address State Name']);
                unset($row['Provider Business Mailing Address Postal Code']);
                unset($row['Provider Business Mailing Address Country Code (If outside U.S.)']);

                if (trim($row['Provider First Line Business Practice Location Address'])) {
                    $json['addresses'][] =
                        [
                            'type' => 'physical',
                            'first_line' => $row['Provider First Line Business Practice Location Address'],
                            'second_line' => $row['Provider Second Line Business Practice Location Address'],
                            'city' => $row['Provider Business Practice Location Address City Name'],
                            'state' => $row['Provider Business Practice Location Address State Name'],
                            'postal_code' => $row['Provider Business Practice Location Address Postal Code'],
                            'country' => trim($row['Provider Business Practice Location Address Country Code (If outside U.S.)']) ?? 'US',

                        ];
                }
                if (trim($row['Provider Business Mailing Address Telephone Number'])) {
                    $json['phone_numbers'][] = ['type' => 'phone', 'number' => $row['Provider Business Mailing Address Telephone Number']];
                }
                if (trim($row['Provider Business Mailing Address Fax Number'])) {
                    $json['phone_numbers'][] = ['type' => 'fax', 'number' => $row['Provider Business Mailing Address Fax Number']];
                }
                if (trim($row['Provider Business Practice Location Address Telephone Number'])) {
                    $json['phone_numbers'][] = ['type' => 'phone', 'number' => $row['Provider Business Practice Location Address Telephone Number']];
                }
                if (trim($row['Provider Business Mailing Address Fax Number'])) {
                    $json['phone_numbers'][] = ['type' => 'fax', 'number' => $row['Provider Business Mailing Address Fax Number']];
                }

                unset($row['Provider First Line Business Practice Location Address']);
                unset($row['Provider Second Line Business Practice Location Address']);
                unset($row['Provider Business Practice Location Address City Name']);
                unset($row['Provider Business Practice Locationg Address State Name']);
                unset($row['Provider Business Practice Location Address Postal Code']);
                unset($row['Provider Business Practice Location Address Country Code (If outside U.S.)']);

                unset($row['Provider Business Mailing Address Telephone Number']);
                unset($row['Provider Business Mailing Address Fax Number']);
                unset($row['Provider Business Practice Location Address Telephone Number']);
                unset($row['Provider Business Mailing Address Fax Number']);


                for ($x = 1; $x <= 15; $x++) {
                    if (trim($row['Healthcare Provider Taxonomy Code_' . $x])) {
                        $subrow = [
                            'taxonomy_code' => $row['Healthcare Provider Taxonomy Code_' . $x],
                            'license_number' => $row['Provider License Number_' . $x],
                            'license_state' => $row['Provider License Number State Code_' . $x],
                            'is_primary' => $row['Healthcare Provider Primary Taxonomy Switch_' . $x],
                            'grouping' => $taxonomy[$row['Healthcare Provider Taxonomy Code_' . $x]]['grouping'],
                            'classification' => $taxonomy[$row['Healthcare Provider Taxonomy Code_' . $x]]['classification'],
                            'specialization' => $taxonomy[$row['Healthcare Provider Taxonomy Code_' . $x]]['specialization'],
                            'definition' => $taxonomy[$row['Healthcare Provider Taxonomy Code_' . $x]]['definition'],
                            'medical_specialty_code' => $taxonomy[$row['Healthcare Provider Taxonomy Code_' . $x]]['medical_specialty_code'],

                        ];

                        $json['specialties'][] = $subrow;
                        if ($row['Healthcare Provider Primary Taxonomy Switch_' . $x] == 'Y') {
                            $json['taxonomy_code'] = $row['Healthcare Provider Taxonomy Code_' . $x];
                            $json['license_number'] = $row['Provider License Number_' . $x];
                            $json['license_state'] = $row['Provider License Number State Code_' . $x];
                            $json['is_primary'] = $row['Healthcare Provider Primary Taxonomy Switch_' . $x];
                            $json['grouping'] = $taxonomy[$row['Healthcare Provider Taxonomy Code_' . $x]]['grouping'];
                            $json['classification'] = $taxonomy[$row['Healthcare Provider Taxonomy Code_' . $x]]['classification'];
                            $json['specialization'] = $taxonomy[$row['Healthcare Provider Taxonomy Code_' . $x]]['specialization'];
                            $json['definition'] = $taxonomy[$row['Healthcare Provider Taxonomy Code_' . $x]]['definition'];
                            $json['medical_specialty_code'] = $taxonomy[$row['Healthcare Provider Taxonomy Code_' . $x]]['medical_specialty_code'];
                        }
                    }

                    unset($row['Healthcare Provider Taxonomy Code_' . $x]);
                    unset($row['Provider License Number_' . $x]);
                    unset($row['Provider License Number State Code_' . $x]);
                    unset($row['Healthcare Provider Primary Taxonomy Switch_' . $x]);

                }
                for ($x = 1; $x <= 50; $x++) {
                    if (trim($row['Other Provider Identifier_' . $x])) {
                        $subrow = [

                            'identifier' => $row['Other Provider Identifier_' . $x],
                            'type_code' => $row['Other Provider Identifier Type Code_' . $x],
                            'state' => $row['Other Provider Identifier State_' . $x],
                            'issuer' => $row['Other Provider Identifier Issuer_' . $x]

                        ];
                        $json['other_provider'][] = $subrow;
                    }

                    unset($row['Other Provider Identifier_' . $x]);
                    unset($row['Other Provider Identifier Type Code_' . $x]);
                    unset($row['Other Provider Identifier State_' . $x]);
                    unset($row['Other Provider Identifier Issuer_' . $x]);

                }
                for ($x = 1; $x <= 15; $x++) {
                    if (trim($row['Healthcare Provider Taxonomy Group_' . $x])) {
                        $subrow = [

                            $row['Healthcare Provider Taxonomy Group_' . $x],


                        ];

                        $json['taxonomy_group'][] = $subrow;

                    }
                    if ($x > 1) {
                        unset($row['Healthcare Provider Taxonomy Group_' . $x]);
                    }
                }

                unset($row);

                $this->npi__($json);
                unset($json);
                if ($this->ct % 25000 == 0) {
                    buffer_progress($this->ct/$this->totalFileLines,'progressbar');
                    live_flush(number_format($this->ct, 0), 'count');
                    $this->outfileFileName = sprintf(__DIR__ . '/../../export/%s_%d.json', $this->session, $this->outFileCount);
                    live_flush($this->outfileFileName, 'file');

                }


            }
            $this->npi__(false, true);
            $this->outfileFileFilter = sprintf( '/import/"%s_*.json"', $this->session);

            $readyImport = glob($this->outfileFileFilter);

            foreach($readyImport as $file){
                 $status = $this->mongoimport('base', 'national_provider', $file, 'json', 'upsert', true,'npi');
                if ($status === true) {
                    live_flush("successfully upserted " . $this->outfileFileName, 'upsert');
                    unlink($file);
                    $cmd=sprintf('find . -name %s -exec  mongoimport --ssl --host chroniclehealth-primary.cluster-cdqcr83pkeam.us-west-2.docdb.amazonaws.com:27017 --sslCAFile /home/ubuntu/rds-combined-ca-bundle.pem --username root --password SkJxY9ubJTNF --db=base --type=json --mode=insert   --collection=national_provider_identifier --numInsertionWorkers=8 --jsonArray --file=\'{}\' \;',  $this->outfileFileFilter );
                    live_append($cmd, 'command');
                } else {
                    live_flush("falied to upsert " . $this->outfileFileName, 'upsert');
                }
            }



//            $cmd=sprintf('find . -name %s -exec  mongoimport --ssl --host chroniclehealth-primary.cluster-cdqcr83pkeam.us-west-2.docdb.amazonaws.com:27017 --sslCAFile /home/ubuntu/rds-combined-ca-bundle.pem --username root --password SkJxY9ubJTNF --db=base --type=json --mode=upsert --upsertFields npi  --collection=nppes --numInsertionWorkers=8 --jsonArray --file=\'{}\' \;',  $this->outfileFileFilter );
//            $cmd=sprintf('find . -name %s -exec  mongoimport --ssl --host chroniclehealth-primary.cluster-cdqcr83pkeam.us-west-2.docdb.amazonaws.com:27017 --sslCAFile /home/ubuntu/rds-combined-ca-bundle.pem --username root --password SkJxY9ubJTNF --db=base --type=json --mode=insert   --collection=national_provider_identifier --numInsertionWorkers=8 --jsonArray --file=\'{}\' \;',  $this->outfileFileFilter );
//            live_flush($cmd, 'command');
//
//            exec($cmd,$outputArray,$result);
//            live_append($result==0 ? 'Import Succeeded' : 'Import Failed','command');

        }


    }

    protected function npi__($row, $close = false)
    {

        if ($this->outFile === false) {
            $this->outFile = [];
        } else {
            if ($close == true || $this->ct % $this->maxFileSize == 0) {
                $this->outFileCount++;
                $this->outfileFileName = sprintf( '/import/%s_%d.json', $this->session, $this->outFileCount);
                file_put_contents($this->outfileFileName, json_encode($this->outFile));

                live_flush($this->outfileFileName, 'file');
                $this->outFile = [];
            }
        }
        if ($row != false) {
            $this->outFile[] = $row;
        }

        
        // if($close == true  || $this->filesizeLimit($this->human_filesize(mb_strlen(json_encode($this->outFile), '8bit') ),'15 MB')===false){

    }


    /**
     * main provider table
     * @param $row
     */

    protected function ajax()
    {
        $this->_format = 'plainjson';
        $this->_result = array();


    }

    protected function help()
    {
        $this->_format = 'html';
        $this->_view = 'home/helpss';
        exit;
    }

    protected function view()
    {
//        $tables = ['nppes_data', 'nppes_healthcare_provider', 'nppes_other_provider_identifier', 'nppes_healthcare_provider_taxonomy_group'];
//
//        foreach ($tables as $table) {
//            $this->_result['tables'][$table] = $this->getETLVersions($table);
//        }


        $this->_result['latest'] = $this->check_updated_file();
        switch ($this->check_updated_file($this->dateString)) {
            case 1:
                $this->_result['latest'] = "Eligible File  Found for $this->dateString: ";
                break;
            case 0:
                $this->_result['latest'] = "Eligible File  NOT found for $this->dateString: ";
                break;
            case -1:
                $this->_result['latest'] = "Latest file already loaded: ";
                break;
        }

        $this->_format = 'html';
        $this->_view = 'nppes_etl/view';

        exit;
    }

    protected function check_updated_file()
    {

        $url = sprintf('http://download.cms.gov/nppes/NPPES_Data_Dissemination_%s.zip', $this->dateString);
//        $sql="SELECT count(*) from utility.etl_logs where message=:filename";
//        $params[':filename']=$url;
//
//        $alreadyLoaded = $this->read($this->_read,$sql,$params,'fetch',PDO::FETCH_COLUMN);
//        if($alreadyLoaded >=1){
//            return -1;
//        }
        $this->file_url = $url;
        $header_response = get_headers($url, 1);

        if (strpos($header_response[0], "404") !== false) {
            return 0;

        } else {
            return 1;
        }
    }

}

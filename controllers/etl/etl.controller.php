<?php

use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use MongoDB\Driver\Manager;
use TheCodingMachine\GraphQLite\SchemaFactory;
use TheCodingMachine\GraphQLite\Context\Context;


use Atanvarno\Dependency\Container;
use function Atanvarno\Dependency\{entry, factory, object, value};

error_reporting(E_ALL);
ini_set('display_errors', 1);

set_time_limit(0);


// coll2.find({practice_state: { "$regex" : "tx" , "$options" : "i"}})^

/**
 * @Type()
 */
class etl extends service
{

    public $errors = array();
    public $is_api = true;

    public function __construct()
    {


        $this->allowed_actions = array('import', 'view', 'ajax');

        $this->_preprocess();
        $this->_format = 'json';
    }

    protected function ajax()
    {
        $this->_format = 'plainjson';
        $this->_result = array();


    }


    protected function mapIndividualProvider($row)
    {
        $map = [

            'npi' => 'npi',
            'replacement_npi' => 'replacement_npi',
            'employer_identification_number' => 'employer_identification_number',
            'provider_last_name' => 'last_name',
            'provider_first_name' => 'first_name',
            'provider_middle_name' => 'middle_name',
            'provider_name_prefix_text' => 'name_prefix',
            'provider_name_suffix_text' => 'name_suffix',
            'provider_credential_text' => 'credential',
            'provider_other_last_name' => 'other_last_name',
            'provider_other_first_name' => 'other_first_name',
            'provider_other_middle_name' => 'other_middle_name',
            'provider_other_name_prefix_text' => 'other_name_prefix',
            'provider_other_name_suffix_text' => 'other_name_suffix',
            'provider_other_credential_text' => 'other_credential',
            'provider_other_last_name_type_code' => 'other_last_name_type_code',
            'provider_first_line_business_mailing_address' => 'first_line_mailing_address',
            'provider_second_line_business_mailing_address' => 'second_line_mailing_address',
            'provider_business_mailing_address_city_name' => 'mailing_address_city_name',
            'provider_business_mailing_address_state_name' => 'mailing_address_state_name',
            'provider_business_mailing_address_postal_code' => 'mailing_address_postal_code',
            'provider_business_mailing_address_country_code' => 'mailing_address_country_code',
            'provider_business_mailing_address_telephone_number' => 'mailing_address_telephone_number',
            'provider_business_mailing_address_fax_number' => 'mailing_address_fax_number',
            'provider_first_line_business_practice_location_address' => 'first_line_practice_location_address',
            'provider_second_line_business_practice_location_address' => 'second_line_practice_location_address',
            'provider_business_practice_location_address_city_name' => 'practice_location_address_city_name',
            'provider_business_practice_location_address_state_name' => 'practice_location_address_state_name',
            'provider_business_practice_location_address_postal_code' => 'practice_location_address_postal_code',
            'provider_business_practice_location_address_country_code' => 'practice_location_address_country_code',
            'provider_business_practice_location_address_telephone_number' => 'practice_location_address_telephone_number',
            'provider_business_practice_location_address_fax_number' => 'practice_location_address_fax_number',
            'provider_enumeration_date' => 'enumeration_date',
            'last_update_date' => 'last_update_date',
            'npi_deactivation_reason_code' => 'npi_deactivation_reason_code',
            'npi_deactivation_date' => 'npi_deactivation_date',
            'npi_reactivation_date' => 'npi_reactivation_date',
            'provider_gender_code' => 'gender'


        ];

        $specialtyMap = [
            'healthcare_provider_taxonomy_code' => 'healthcare_taxonomy_code',
            'provider_license_number' => 'license_number',
            'provider_license_number_state_code' => 'license_number_state_code',
            'healthcare_provider_primary_taxonomy_switch' => 'is_primary',

        ];
        $licenseMap = [
            'other_provider_identifier' => 'other_identifier',
            'other_provider_identifier_type_code' => 'other_identifier_type_code',
            'other_provider_identifier_state' => 'other_identifier_state',
            'other_provider_identifier_issuer' => 'other_identifier_issuer'
        ];
        $return = [];

        foreach ($map as $find => $name) {
            $return[$name] = $row[$find];

        }
        $return['specialties'] = [];
        $sql = "SELECT 
                    p.healthcare_provider_taxonomy_code as taxonomy_code, 
                    p.provider_license_number as license_number, 
                    p.provider_license_number_state_code as state_code,
                    p.healthcare_provider_primary_taxonomy_switch as is_primary, 
                    c.grouping, 
                    c.classification, 
                    c.specialization, 
                    c.definition, 
                    c.notes, 
                    c.medical_specialty_code  
                from nppes_healthcare_provider p 
                    join nppes_taxonomy_codes c 
                        on c.code = p.healthcare_provider_taxonomy_code 
                where p.npi=:npi";
        $params = [':npi' => $row['npi']];
        $return['specialties'] = $this->query('_redshift', $sql, $params);


        $sql = "SELECT * from nppes_other_provider_identifier where npi=:npi";
        $params = [':npi' => $row['npi']];
        $return['licenses'] = $this->query('_redshift', $sql, $params);


        $sql = "SELECT 
                   group_pac_id,
                   group_enrollment_id,
                   group_legal_business_name,
                   group_state_code,
                   group_due_date,
                   group_reassignments,
                   record_type,
                   individual_enrollment_id as enrollment_id,
                   individual_due_date,
                   individual_associations as associations
                  
            FROM nppes_groups where  individual_npi = :npi;";
        $params = [':npi' => $row['npi']];
        $return['group_associations'] = $this->query('_redshift', $sql, $params);

        $sql = "SELECT 
                       drug_name,
                       generic_name,
                       bene_count,
                       total_claim_count,
                       total_30_day_fill_count,
                       total_day_supply,
                       total_drug_cost,
                       bene_count_ge65,
                       bene_count_ge65_suppress_flag,
                       total_claim_count_ge65,
                       ge65_suppress_flag,
                       total_30_day_fill_count_ge65,
                       total_day_supply_ge65,
                       total_drug_cost_ge65,
                       billing_year
                FROM medicare_provider_part_d where npi=:npi order by billing_year DESC;";
        $params = [':npi' => $row['npi']];
        $return['medicare_billing'] = $this->query('_redshift', $sql, $params);


        return $return;


    }

    protected function mapPracticeProvider($row)
    {
        $map = [

            'npi' => 'npi',
            'replacement_npi' => 'replacement_npi',
            'provider_organization_name' => 'organization_name',
            'provider_other_organization_name' => 'other_organization_name',
            'provider_other_organization_name_type_code' => 'other_organization_name_type_code',
            'provider_first_line_business_mailing_address' => 'first_line_mailing_address',
            'provider_second_line_business_mailing_address' => 'second_line_mailing_address',
            'provider_business_mailing_address_city_name' => 'mailing_address_city_name',
            'provider_business_mailing_address_state_name' => 'mailing_address_state_name',
            'provider_business_mailing_address_postal_code' => 'mailing_address_postal_code',
            'provider_business_mailing_address_country_code' => 'mailing_address_country_code',
            'provider_business_mailing_address_telephone_number' => 'mailing_address_telephone_number',
            'provider_business_mailing_address_fax_number' => 'mailing_address_fax_number',
            'provider_first_line_business_practice_location_address' => 'first_line_practice_location_address',
            'provider_second_line_business_practice_location_address' => 'second_line_practice_location_address',
            'provider_business_practice_location_address_city_name' => 'practice_location_address_city_name',
            'provider_business_practice_location_address_state_name' => 'practice_location_address_state_name',
            'provider_business_practice_location_address_postal_code' => 'practice_location_address_postal_code',
            'provider_business_practice_location_address_country_code' => 'practice_location_address_country_code',
            'provider_business_practice_location_address_telephone_number' => 'practice_location_address_telephone_number',
            'provider_business_practice_location_address_fax_number' => 'practice_location_address_fax_number',
            'provider_enumeration_date' => 'enumeration_date',
            'last_update_date' => 'last_update_date',
            'npi_deactivation_reason_code' => 'npi_deactivation_reason_code',
            'npi_deactivation_date' => 'npi_deactivation_date',
            'npi_reactivation_date' => 'npi_reactivation_date',
            'provider_gender_code' => 'gender_code',
            'authorized_official_last_name' => 'authorized_official_last_name',
            'authorized_official_first_name' => 'authorized_official_first_name',
            'authorized_official_middle_name' => 'authorized_official_middle_name',
            'authorized_official_title_or_position' => 'authorized_official_title_or_position',
            'authorized_official_telephone_number' => 'authorized_official_telephone_number',
            'healthcare_provider_taxonomy_code' => 'healthcare_taxonomy_code',
            'provider_license_number' => 'license_number',
            'provider_license_number_state_code' => 'license_number_state_code',
            'healthcare_provider_primary_taxonomy_switch' => 'healthcare_primary_taxonomy_switch',
            'other_provider_identifier' => 'other_identifier',
            'other_provider_identifier_type_code' => 'other_identifier_type_code',
            'other_provider_identifier_state' => 'other_identifier_state',
            'other_provider_identifier_issuer' => 'other_identifier_issuer',
            'is_sole_proprietor' => 'is_sole_proprietor',
            'is_organization_subpart' => 'is_organization_subpart',
            'parent_organization_lbn' => 'parent_organization_lbn',
            'parent_organization_tin' => 'parent_organization_tin',
            'authorized_official_name_prefix_text' => 'authorized_official_name_prefix_text',
            'authorized_official_name_suffix_text' => 'authorized_official_name_suffix_text',
            'authorized_official_credential_text' => 'authorized_official_credential_text',
            'healthcare_provider_taxonomy_group' => 'healthcare_taxonomy_group',


        ];

        $specialtyMap = [
            'healthcare_provider_taxonomy_code' => 'healthcare_taxonomy_code',
            'provider_license_number' => 'license_number',
            'provider_license_number_state_code' => 'license_number_state_code',
            'healthcare_provider_primary_taxonomy_switch' => 'is_primary',

        ];
        $licenseMap = [
            'other_provider_identifier' => 'other_identifier',
            'other_provider_identifier_type_code' => 'other_identifier_type_code',
            'other_provider_identifier_state' => 'other_identifier_state',
            'other_provider_identifier_issuer' => 'other_identifier_issuer'
        ];
        $return = [];

        foreach ($map as $find => $name) {
            $return[$name] = $row[$find];

        }
        $return['specialties'] = [];
        $sql = "SELECT 
                    p.healthcare_provider_taxonomy_code as taxonomy_code, 
                    p.provider_license_number as license_number, 
                    p.provider_license_number_state_code as state_code,
                    p.healthcare_provider_primary_taxonomy_switch as is_primary, 
                    c.grouping, 
                    c.classification, 
                    c.specialization, 
                    c.definition, 
                    c.notes, 
                    c.medical_specialty_code  
                from nppes_healthcare_provider p 
                    join nppes_taxonomy_codes c 
                        on c.code = p.healthcare_provider_taxonomy_code 
                where p.npi=:npi";
        $params = [':npi' => $row['npi']];
        $return['specialties'] = $this->query('_redshift', $sql, $params);


        $sql = "SELECT * from nppes_other_provider_identifier where npi=:npi";
        $params = [':npi' => $row['npi']];
        $return['licenses'] = $this->query('_redshift', $sql, $params);


        $sql = "SELECT 
                       drug_name,
                       generic_name,
                       bene_count,
                       total_claim_count,
                       total_30_day_fill_count,
                       total_day_supply,
                       total_drug_cost,
                       bene_count_ge65,
                       bene_count_ge65_suppress_flag,
                       total_claim_count_ge65,
                       ge65_suppress_flag,
                       total_30_day_fill_count_ge65,
                       total_day_supply_ge65,
                       total_drug_cost_ge65,
                       billing_year
                FROM medicare_provider_part_d where npi=:npi order by billing_year DESC;";
        $params = [':npi' => $row['npi']];
        $return['medicare_billing'] = $this->query('_redshift', $sql, $params);


        return $return;


    }


    protected function import()
    {
        $start = microtime(true);
        live_template('query/view');
        $this->_format = 'json';

        $total = $this->getRecordsCount('nppes_mongo_base_1580919497.csv');


        $client = new Aws\S3\S3Client(array(
            'credentials' => array(
                'key' => AWS_ACCESS_KEY_ID_LYTICS,
                'secret' => AWS_SECRET_ACCESS_KEY_LYTICS,
            ),
            'region' => 'us-west-2',
            'version' => 'latest'
        ));


        $client->registerStreamWrapper();
        $current = 0;
        if ($stream = fopen('s3://qorpus-assets/nppes_mongo_provider_1580919854.csv', 'r')) {
            // While the stream is still open
            $header = fgetcsv($stream);
            $collection = [];
            $documents=[];

            while (!feof($stream)) {

                $row = array_combine($header, fgetcsv($stream));
                $documents[]=$row;
                if(count($documents) == 100){
                    $this->mongoBulkUpsert('nppes_providers', 'npi', $documents);
                    $documents=[];
                    $current++;
                }


                if ($current % 10 === 0) {
                    live_flush($row['npi'], 'record');
                    $pct = abs(100 - (($current / $total) * 100));
                    $remaining = $total - $current;
                    live_flush($remaining . " [$pct]", 'remaining');

                    $elapsed = (microtime(true) - $start) / $current;

                    live_flush($elapsed, 'rps');
                }


            }
//            // Be sure to close the stream resource when you're done with it
            fclose($stream);
        } else {

        }
    }

    protected function getRecordsCount($key)
    {
        $s3Client = S3Client::factory(array(
            'credentials' => array(
                'key' => AWS_ACCESS_KEY_ID_LYTICS,
                'secret' => AWS_SECRET_ACCESS_KEY_LYTICS,

            ),
            'region' => 'us-west-2',
            'version' => 'latest'
        ));
        $result = $s3Client->selectObjectContent([
            'Bucket' => AWS_BUCKET_LYTICS,
            'Key' => $key,
            'ExpressionType' => 'SQL',
            'Expression' => 'SELECT count(npi) FROM S3Object ',
            'InputSerialization' => [
                'CSV' => [
                    'FileHeaderInfo' => 'USE',
                    'RecordDelimiter' => "\n",
                    'FieldDelimiter' => ',',
                ],
            ],
            'OutputSerialization' => [
                'CSV' => [],
            ],
        ]);
        foreach ($result['Payload'] as $event) {
            if (isset($event['Records'])) {
                $records = (string)$event['Records']['Payload'];
                $count = intval($records);
                $this->_result['records'] = number_format($count, 0);
                live_flush("Records Exported: " . number_format($count, 0), "exported");
            }
        }
        return $count;
    }

    protected function view()
    {
        $start = microtime(true);
        live_template('query/view');
        $max = (int)file_get_contents('/var/log/md.log');

       $sql='SELECT 
                    p.npi,
                    p.healthcare_provider_taxonomy_code as taxonomy_code, 
                    p.provider_license_number as license_number, 
                    p.provider_license_number_state_code as state_code,
                    p.healthcare_provider_primary_taxonomy_switch as is_primary, 
                    c.grouping, 
                    c.classification, 
                    c.specialization, 
                    c.definition, 
                    c.medical_specialty_code  
                from nppes_healthcare_provider p 
                    join nppes_taxonomy_codes c 
                        on c.code = p.healthcare_provider_taxonomy_code ';
       $data = $this->query('_redshift',$sql);
        live_flush(count($data), 'record');
       $taxonomy_map=[];
       $idx=0;
       foreach($data as $x =>$row){

           if(!isset($taxonomy_map[$row['npi']])){
               $taxonomy_map[$row['npi']]=[];
           }
           $taxonomy_map[$row['npi']][]=$row;
           if($idx % 1000 == 0){
               live_flush($idx, 'record');
           }
           unset($data[$x]);
       }



    }


}

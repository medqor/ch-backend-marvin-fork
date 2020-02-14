<?php


error_reporting(E_ALL);
ini_set('display_errors', true);
ignore_user_abort();

use Aws\S3\S3Client;
use Aws\S3\Model\ClearBucket;


set_time_limit(0);

class hubspot_etl extends service
{
    public $report;
    public $maxFileSize = 5000;
    public $errors = 0;
    public $total = 0;
    public $year = 2017;
    public $showCSVCreate = true;
    public $file_url;
    public $dateString;
    public $outFileCount = 0;
    public $outFile = false;
    public $lastCount = 0;
    public $hubspot_map = ['contact_id',
        'first_name',
        'last_name',
        'name',
        'email',
        'phone_number',
        'contact_owner',
        'last_activity_date',
        'last_contacted',
        'create_date',
        'npi',
        'street_address',
        'street_address_2',
        'city',
        'state_region',
        'postal_code',
        'website_url',
        'marketing_emails_opened',
        'marketing_emails_delivered',
        'marketing_emails_clicked',
        'marketing_emails_bounced',
        'marketing_email_confirmation_status',
        'average_pageviews',
        'number_of_pageviews',
        'number_of_visits',
        'company_name',
        'date_of_birth',
        'degree',
        'email_confirmed',
        'fax_number',
        'gender',
        'graduation_date',
        'job_title',
        'job_function',
        'hubspot_score',
        'marital_status',
        'military_status',
        'mobile_phone_number',
        'school',
        'relationship_status',
        'twitter_username',
        'work_email',
        'last_modified_date',
        'industry',
        'follower_count',
        'klout_score',
        'linkedin_bio',
        'linkedin_connections',
        'twitter_bio',
        'twitter_profile_photo',
        'x7_3rd_party',
        'x7_sales',
        'clp_3rd_party',
        'clp_sales',
        'hr_3rd_party',
        'hr_sales',
        'op_3rd_party',
        'op_sales',
        'psp_3rd_party',
        'psp_sales',
        'ptp_3rd_party',
        'ptp_sales',
        'rm_3rd_party',
        'rm_sales',
        'rt_3rd_party',
        'rt_sales',
        'sr_3rd_party',
        'sr_sales',
        'axis_3rd_party',
        'axis_sales',
        'opt_in_advertiser_communication',
        'opt_in_internal_communication',
        'opted_out_of_email__marketing_information',
        'opted_out_of_email__24x7_marketing_info',
        'opted_out_of_email__24x7_partner_content_distribution',
        'opted_out_of_email__24x7_e_newsletters',
        'opted_out_of_email__axis_partner_content_distribution',
        'opted_out_of_email__axis_marketing_info',
        'opted_out_of_email__axis_e_newsletters',
        'opted_out_of_email__clp_marketing_info',
        'opted_out_of_email__clp_partner_content_distribution',
        'opted_out_of_email__clp_e_newsletters',
        'opted_out_of_email__medqor_sales_and_information',
        'opted_out_of_email__one_to_one',
        'opted_out_of_email__orthodontic_products_marketing_info',
        'opted_out_of_email__orthodontic_products_partner_content_distribution',
        'opted_out_of_email__orthodontic_products_e_newsletters',
        'opted_out_of_email__physical_therapy_products_marketing_information',
        'opted_out_of_email__physical_therapy_products_partner_content_distibution',
        'opted_out_of_email__physical_therapy_products_e_newsletters',
        'opted_out_of_email__plastic_surgery_practice_partner_content_distribution',
        'opted_out_of_email__plastic_surgery_practice_e_newsletters',
        'opted_out_of_email__plastic_surgery_practtice_marketing_information',
        'opted_out_of_email__rt_magazine_marketing_information',
        'opted_out_of_email__rt_magazine_partner_content_distribution',
        'opted_out_of_email__rt_magazine_e_newsletters',
        'opted_out_of_email__rehab_managememt_partner_content_distribution',
        'opted_out_of_email__rehab_management_marketing_information',
        'opted_out_of_email__rehab_management_e_newsletters',
        'opted_out_of_email__sleep_review_marketing_information',
        'opted_out_of_email__sleep_review_partner_content_distribution',
        'opted_out_of_email__sleep_review_e_newsletters',
        'opted_out_of_email__the_hearing_review_marketing_info',
        'opted_out_of_email__the_hearing_review_partner_content_distribution',
        'opted_out_of_email__the_hearing_review_e_newsletters',
        'sends_since_last_engagement',
        'unsubscribed_from_all_email',
        'x7_business_code',
        'x7_business_other',
        'x7_circ_number',
        'x7_editorial',
        'x7_email_promo_sub',
        'x7_expert_insight',
        'x7_facility_code',
        'x7_facility_other',
        'x7_function_code',
        'x7_function_other',
        'x7_htm_occupation_dont_use',
        'x7_imaging_tech_update',
        'x7_jolt',
        'x7_marketing',
        'x7_print',
        'x7_promocode',
        'x7_purchasing',
        'x7_recommend',
        'x7_renewal',
        'x7_subscribe_reverify',
        'x7_top_10',
        'clp_business_code',
        'clp_business_other',
        'clp_circ_number',
        'clp_editorial',
        'clp_email_promo_sub',
        'clp_expert_insight',
        'clp_facility_beds',
        'clp_facility_type',
        'clp_facility_type_other',
        'clp_function_code',
        'clp_function_other',
        'clp_hospital_code',
        'clp_marketing',
        'clp_prime',
        'clp_promocode',
        'clp_recommend',
        'clp_renewal',
        'clp_subscribe_reverify',
        'clp_top_10',
        'hr__aids_dispensed',
        'hr_aids_yesno',
        'hr_budget',
        'hr_business_code',
        'hr_business_other',
        'hr_carecredit_editorial',
        'hr_circ_number',
        'hr_editorial',
        'hr_email_promo_sub',
        'hr_expert_insight',
        'hr_forum',
        'hr_function_code',
        'hr_function_other',
        'hr_insider',
        'hr_marketing',
        'hr_promocode',
        'hr_purchase',
        'hr_purchase_other',
        'hr_renewal',
        'hr_subscribe_reverify',
        'hr_top_10',
        'op_budget',
        'op_circ_number',
        'op_editorial',
        'op_email_promo_sub',
        'op_expert_insight',
        'op_employ',
        'op_function_code',
        'op_function_other',
        'op_marketing',
        'op_newsbites',
        'op_practice_code',
        'op_print',
        'op_promocode',
        'op_purchase',
        'op_recommend',
        'op_renewal',
        'op_source',
        'op_subscribe_reverify',
        'op_top_10',
        'psp_circ_number',
        'psp_e_report',
        'psp_email_promo_sub',
        'psp_expert_insight',
        'psp_facility',
        'psp_facility_other',
        'psp_function_code',
        'psp_function_other',
        'psp_marketing',
        'psp_recommend',
        'psp_subscribe_reverify',
        'psp_top_10',
        'ptp_budget',
        'ptp_business',
        'ptp_business_other',
        'ptp_circ_number',
        'ptp_editorial',
        'ptp_email_promo_sub',
        'ptp_expert_insight',
        'ptp_function_code',
        'ptp_function_other',
        'ptp_marketing',
        'ptp_promocode',
        'ptp_purchase',
        'ptp_purchase_other',
        'ptp_recommend',
        'ptp_renewal',
        'ptp_soap_notes',
        'ptp_subscribe_reverify',
        'ptp_top_10',
        'rm_budget',
        'rm_business_code',
        'rm_business_other',
        'rm_circ_number',
        'rm_editorial',
        'rm_email_promo_sub',
        'rm_expert_insight',
        'rm_function_code',
        'rm_function_other',
        'rm_marketing',
        'rm_print',
        'rm_promocode',
        'rm_purchase',
        'rm_recommend',
        'rm_renewal',
        'rm_subscribe_reverify',
        'rm_today',
        'rm_top_10',
        'rt_aarc_number',
        'rt_budget',
        'rt_business_code',
        'rt_business_other',
        'rt_copd',
        'rt_circ_marketing',
        'rt_circ_number',
        'rt_circ_source',
        'rt_editorial',
        'rt_email_promo_sub',
        'rt_expert_insight',
        'rt_function_code',
        'rt_function_other',
        'rt_marketing',
        'rt_now',
        'rt_print',
        'rt_promocode',
        'rt_purchase',
        'rt_respiratory_report',
        'rt_renewal',
        'rt_subscribe_reverify',
        'rt_top_10',
        'sr_business_code',
        'sr_business_other',
        'sr_circ_number',
        'sr_credentials',
        'sr_daily',
        'sr_dental_sleep_center',
        'sr_expert_insight',
        'sr_editorial',
        'sr_email_promo_sub',
        'sr_function_code',
        'sr_function_other',
        'sr_jazz_3rd_party',
        'sr_marketing',
        'sr_narcolepsy',
        'sr_print',
        'sr_recommend',
        'sr_promocode',
        'sr_renewal',
        'sr_sleep_report',
        'sr_subscribe_reverify',
        'sr_top_10',
        'axis_advisor',
        'axis_budget',
        'axis_circ_number',
        'axis_editorial',
        'axis_facility_code',
        'axis_facility_other',
        'axis_function_code',
        'axis_function_other',
        'axis_imaging_insider',
        'axis_it_3rd_party',
        'axis_it_report',
        'axis_marketing',
        'axis_purchase',
        'axis_purchase_other',
        'axis_recommend',
        'axis_tech_edge',
        'axis_top_10',
        'x7_qual_date',
        'clp_qual_date',
        'hr_qual_date',
        'op_qual_date',
        'psp_qual_date',
        'ptp_qual_date',
        'rm_qual_date',
        'rt_qual_date',
        'sr_qual_date',
        'axis_qual_date',
        'x7_daily',
        'clp_daily',
        'hr_daily',
        'op_daily',
        'psp_daily',
        'ptp_daily',
        'rm_daily',
        'axis_daily',
        'associated_company_id',
        'associated_company',
        'etldate'];
    public $rows_updated = ['hubspot' => 0, 'nppes' => 0];
    public $nppes_map = ['contact_id' => 'contact_id',
        'first_name' => 'first_name',
        'last_name' => 'last_name',
        'name' => 'name',
        'email' => 'email',
        'phone_number' => 'phone_number',
        'last_activity_date' => 'last_activity_date',
        'last_contacted' => 'last_contacted',
        'create_date' => 'create_date',
        'street_address' => 'address',
        'street_address_2' => 'address_2',
        'city' => 'city',
        'state_region' => 'state',
        'postal_code' => 'postal_code',
        'website_url' => 'website',
        'company_name' => 'company_name',
        'date_of_birth' => 'date_of_birth',
        'degree' => 'credentials',
        'fax_number' => 'fax_number',
        'gender' => 'gender',
        'graduation_date' => 'graduation_date',
        'job_title' => 'job_title',
        'job_function' => 'job_function',
        'hubspot_score' => 'hubspot_score',
        'marital_status' => 'marital_status',
        'military_status' => 'military_status',
        'mobile_phone_number' => 'mobile_phone_number',
        'school' => 'school',
        'relationship_status' => 'relationship_status',
        'twitter_username' => 'twitter_username',
        'work_email' => 'work_email',
        'last_modified_date' => '2019-12-30 17:02',
        'linkedin_bio' => 'linkedin_bio',
        'twitter_bio' => 'twitter_bio',
        'twitter_profile_photo' => 'twitter_profile_photo',
        'opt_in_advertiser_communication' => 'opt_in_advertiser_communication',
        'opt_in_internal_communication' => 'opt_in_internal_communication',
        'sends_since_last_engagement' => 'sends_since_last_engagement',
        'unsubscribed_from_all_email' => 'unsubscribed_from_all_email',
        'associated_company' => 'associated_company',
        'associated_company_id' => 'associated_company_id',];
    public $lytics_files =['nppes','hubspot'];
    public $lytics_headers_written =['nppes'=>false,'hubspot'=>false];
    public $nppes_magazine_map = ['24x7 Magazine' => [
        'x7_3rd_party' => '3rd_party',
        'x7_sales' => 'sales',
        'x7_business_code' => 'business_code',
        'x7_business_other' => 'business_other',
        'x7_circ_number' => 'circ_number',
        'x7_editorial' => 'editorial',
        'x7_email_promo_sub' => 'email_promo_sub',
        'x7_expert_insight' => 'expert_insight',
        'x7_facility_code' => 'facility_code',
        'x7_facility_other' => 'facility_other',
        'x7_function_code' => 'function_code',
        'x7_function_other' => 'function_other',
        'x7_htm_occupation_dont_use' => 'htm_occupation_dont_use',
        'x7_imaging_tech_update' => 'imaging_tech_update',
        'x7_jolt' => 'jolt',
        'x7_marketing' => 'marketing',
        'x7_print' => 'print',
        'x7_promocode' => 'promocode',
        'x7_purchasing' => 'purchasing',
        'x7_recommend' => 'recommend',
        'x7_renewal' => 'renewal',
        'x7_subscribe_reverify' => 'subscribe_reverify',
        'x7_top_10' => 'top_10',
        'x7_qual_date' => 'qual_date',
        'x7_daily' => 'daily',
    ],
        'Clinical Lab Products' => [

            'clp_3rd_party' => '3rd_party',
            'clp_sales' => 'sales',
            'clp_business_code' => 'business_code',
            'clp_business_other' => 'business_other',
            'clp_circ_number' => 'circ_number',
            'clp_editorial' => 'editorial',
            'clp_email_promo_sub' => 'email_promo_sub',
            'clp_expert_insight' => 'expert_insight',
            'clp_facility_beds' => 'facility_beds',
            'clp_facility_type' => 'facility_type',
            'clp_facility_type_other' => 'facility_type_other',
            'clp_function_code' => 'function_code',
            'clp_function_other' => 'function_other',
            'clp_hospital_code' => 'hospital_code',
            'clp_marketing' => 'marketing',
            'clp_prime' => 'prime',
            'clp_promocode' => 'promocode',
            'clp_recommend' => 'recommend',
            'clp_renewal' => 'renewal',
            'clp_subscribe_reverify' => 'subscribe_reverify',
            'clp_top_10' => 'top_10',
            'clp_qual_date' => 'clp_qual_date',
            'clp_daily' => 'daily',
        ],
        'Hearing Review' => [

            'hr_3rd_party' => '3rd_party',
            'hr_sales' => 'sales',
            'hr__aids_dispensed' => 'aids_dispensed',
            'hr_aids_yesno' => 'aids_yesno',
            'hr_budget' => 'budget',
            'hr_business_code' => 'business_code',
            'hr_business_other' => 'business_other',
            'hr_carecredit_editorial' => 'carecredit_editorial',
            'hr_circ_number' => 'circ_number',
            'hr_editorial' => 'editorial',
            'hr_email_promo_sub' => 'email_promo_sub',
            'hr_expert_insight' => 'expert_insight',
            'hr_forum' => 'forum',
            'hr_function_code' => 'function_code',
            'hr_function_other' => 'function_other',
            'hr_insider' => 'insider',
            'hr_marketing' => 'marketing',
            'hr_promocode' => 'promocode',
            'hr_purchase' => 'purchase',
            'hr_purchase_other' => 'purchase_other',
            'hr_renewal' => 'renewal',
            'hr_subscribe_reverify' => 'subscribe_reverify',
            'hr_top_10' => 'hr_top_10',
            'hr_qual_date' => 'qual_date',
            'hr_daily' => 'hr_daily',
        ],
        'Orthodontic Products Online' => [

            'op_3rd_party' => '3rd_party',
            'op_sales' => 'sales',
            'op_budget' => 'budget',
            'op_circ_number' => 'circ_number',
            'op_editorial' => 'editorial',
            'op_email_promo_sub' => 'email_promo_sub',
            'op_expert_insight' => 'expert_insight',
            'op_employ' => 'employ',
            'op_function_code' => 'function_code',
            'op_function_other' => 'function_other',
            'op_marketing' => 'marketing',
            'op_newsbites' => 'newsbites',
            'op_practice_code' => 'practice_code',
            'op_print' => 'print',
            'op_promocode' => 'promocode',
            'op_purchase' => 'purchase',
            'op_recommend' => 'recommend',
            'op_renewal' => 'renewal',
            'op_source' => 'source',
            'op_subscribe_reverify' => 'subscribe_reverify',
            'op_top_10' => 'top_10',
            'op_qual_date' => 'qual_date',
            'op_daily' => 'daily',
        ],
        'Plastic Surgery Practice' => [

            'psp_3rd_party' => '3rd_party',
            'psp_sales' => 'sales',
            'psp_circ_number' => 'circ_number',
            'psp_e_report' => 'e_report',
            'psp_email_promo_sub' => 'email_promo_sub',
            'psp_expert_insight' => 'expert_insight',
            'psp_facility' => 'facility',
            'psp_facility_other' => 'facility_other',
            'psp_function_code' => 'function_code',
            'psp_function_other' => 'function_other',
            'psp_marketing' => 'marketing',
            'psp_recommend' => 'recommend',
            'psp_subscribe_reverify' => 'subscribe_reverify',
            'psp_top_10' => 'top_10',
            'psp_qual_date' => 'qual_date',
            'psp_daily' => 'daily',
        ],
        'Physical Therapy Products' => [

            'ptp_3rd_party' => '3rd_party',
            'ptp_sales' => 'sales',
            'ptp_budget' => 'budget',
            'ptp_business' => 'business',
            'ptp_business_other' => 'business_other',
            'ptp_circ_number' => 'circ_number',
            'ptp_editorial' => 'editorial',
            'ptp_email_promo_sub' => 'email_promo_sub',
            'ptp_expert_insight' => 'expert_insight',
            'ptp_function_code' => 'function_code',
            'ptp_function_other' => 'function_other',
            'ptp_marketing' => 'marketing',
            'ptp_promocode' => 'promocode',
            'ptp_purchase' => 'purchase',
            'ptp_purchase_other' => 'purchase_other',
            'ptp_recommend' => 'recommend',
            'ptp_renewal' => 'renewal',
            'ptp_soap_notes' => 'soap_notes',
            'ptp_subscribe_reverify' => 'subscribe_reverify',
            'ptp_top_10' => 'top_10',
            'ptp_daily' => 'daily',
            'ptp_qual_date' => 'qual_date',
        ],
        'Rehab Management' => [

            'rm_3rd_party' => '3rd_party',
            'rm_sales' => 'sales',
            'rm_budget' => 'budget',
            'rm_business_code' => 'business_code',
            'rm_business_other' => 'business_other',
            'rm_circ_number' => 'circ_number',
            'rm_editorial' => 'editorial',
            'rm_email_promo_sub' => 'email_promo_sub',
            'rm_expert_insight' => 'expert_insight',
            'rm_function_code' => 'function_code',
            'rm_function_other' => 'function_other',
            'rm_marketing' => 'marketing',
            'rm_print' => 'print',
            'rm_promocode' => 'promocode',
            'rm_purchase' => 'purchase',
            'rm_recommend' => 'recommend',
            'rm_renewal' => 'renewal',
            'rm_subscribe_reverify' => 'subscribe_reverify',
            'rm_today' => 'today',
            'rm_top_10' => 'top_10',
            'rm_daily' => 'daily',
            'rm_qual_date' => 'qual_date',
        ],
        'Respiratory Therapy' => [

            'rt_3rd_party' => '3rd_party',
            'rt_sales' => 'sales',
            'rt_aarc_number' => 'aarc_number',
            'rt_budget' => 'budget',
            'rt_business_code' => 'business_code',
            'rt_business_other' => 'business_other',
            'rt_copd' => 'copd',
            'rt_circ_marketing' => 'circ_marketing',
            'rt_circ_number' => 'circ_number',
            'rt_circ_source' => 'circ_source',
            'rt_editorial' => 'editorial',
            'rt_email_promo_sub' => 'email_promo_sub',
            'rt_expert_insight' => 'expert_insight',
            'rt_function_code' => 'function_code',
            'rt_function_other' => 'function_other',
            'rt_marketing' => 'marketing',
            'rt_now' => 'now',
            'rt_print' => 'print',
            'rt_promocode' => 'promocode',
            'rt_purchase' => 'purchase',
            'rt_respiratory_report' => 'respiratory_report',
            'rt_renewal' => 'renewal',
            'rt_subscribe_reverify' => 'subscribe_reverify',
            'rt_top_10' => 'rt_top_10',
            'rt_qual_date' => 'qual_date',
            //'rt_daily' =>'rt_daily',
        ],
        'Sleep Review' => [

            'sr_3rd_party' => '3rd_party',
            'sr_sales' => 'sales',
            'sr_business_code' => 'business_code',
            'sr_business_other' => 'business_other',
            'sr_circ_number' => 'circ_number',
            'sr_credentials' => 'credentials',
            'sr_daily' => 'daily',
            'sr_dental_sleep_center' => 'dental_sleep_center',
            'sr_expert_insight' => 'expert_insight',
            'sr_editorial' => 'editorial',
            'sr_email_promo_sub' => 'email_promo_sub',
            'sr_function_code' => 'function_code',
            'sr_function_other' => 'function_other',
            'sr_jazz_3rd_party' => 'jazz_3rd_party',
            'sr_marketing' => 'marketing',
            'sr_narcolepsy' => 'narcolepsy',
            'sr_print' => 'print',
            'sr_recommend' => 'recommend',
            'sr_promocode' => 'promocode',
            'sr_renewal' => 'renewal',
            'sr_sleep_report' => 'sleep_report',
            'sr_subscribe_reverify' => 'subscribe_reverify',
            'sr_top_10' => 'top_10',
            'sr_qual_date' => 'qual_date',
            'sr_daily' => 'daily',
        ],
        'Axis Imaging News' => [

            'axis_3rd_party' => '3rd_party',
            'axis_sales' => 'sales',
            'axis_advisor' => 'advisor',
            'axis_budget' => 'budget',
            'axis_circ_number' => 'circ_number',
            'axis_editorial' => 'editorial',
            'axis_facility_code' => 'facility_code',
            'axis_facility_other' => 'facility_other',
            'axis_function_code' => 'function_code',
            'axis_function_other' => 'function_other',
            'axis_imaging_insider' => 'imaging_insider',
            'axis_it_3rd_party' => '3rd_party',
            'axis_it_report' => 'report',
            'axis_marketing' => 'marketing',
            'axis_purchase' => 'purchase',
            'axis_purchase_other' => 'purchase_other',
            'axis_recommend' => 'recommend',
            'axis_tech_edge' => 'tech_edge',
            'axis_top_10' => 'top_10',
            'axis_qual_date' => 'qual_date',
            'axis_daily' => 'daily',
        ]
    ];

    public function __construct()
    {
        $this->pheanstalk = new Pheanstalk('127.0.0.1:11300');
        $this->allowed_actions = array('view',  'process');
        $this->_preprocess();
        $this->etldate = date("Y-m-d H:i:s");
        $this->start=strtotime('now');

    }


    /**
     * @param $row
     */
    protected function upsertHubspot($row)
    {
        $row[] = $this->etldate;
        if(count($this->hubspot_map) == count($row)) {
            $document = array_combine($this->hubspot_map, $row);
            $client = (new MongoDB\Client(MONGODB, array(
                'ssl' => true,
                'sslAllowInvalidCertificates' => true
            )))->base->hubspot;


//            $updateResult = $client->updateMany(
//                ['contact_id' => (string)$document['contact_id']],
//                ['$set' => $document]
//            );





            $this->rows_updated['hubspot']++;
            if ($this->lytics_headers_written['hubspot'] == false) {
                fputcsv($this->lytics_files['hubspot'], array_keys($document));
                $this->lytics_headers_written['hubspot'] = true;
            }
            fputcsv($this->lytics_files['hubspot'], ($document));
            if (trim($document['npi'])) {
                $this->upsertNPPES($document);
            }
        }else{
            $this->rows_updated['hubspot']++;
            file_put_contents('/var/log/pheanstalk.log', pretty_print_r(['map'=>$this->hubspot_map,'row'=>$row],true) . "\r\n", FILE_APPEND);

        }
        if ($this->rows_updated['hubspot'] % 5000 == 0) {
            $payload = ['message'=>$this->rows_updated['hubspot']."/".$this->total,'type'=>'html','target'=>'total','tube'=>$this->tube];
            $this->pheanstalk
                ->useTube($this->tube)
                ->put(json_encode($payload));
            $payload = ['value'=>(($this->rows_updated['hubspot']/$this->total)*100),'type'=>'progressbar','target'=>'progressbar','tube'=>$this->tube];
            $this->pheanstalk
                ->useTube($this->tube)
                ->put(json_encode($payload));
            file_put_contents('/var/log/pheanstalk.log', $this->rows_updated['hubspot']."/".$this->total . "\r\n", FILE_APPEND);

        }
    }

    /**
     * @param $row
     */
    protected function upsertNPPES($row)
    {
        $nppes = $this->mongoQuery('nppes', ["npi" => ['$eq' => (string)$row['npi']]],['limit'=>1]);

        $outfile=['npi'=>'',
            'prefix'=>'',
            'first_name'=>'',
            'middle_name'=>'',
            'last_name'=>'',
            'suffix'=>'',
            'creds'=>'',
            'mailing_address'=>'',
            'mailing_address2'=>'',
            'mailing_city'=>'',
            'mailing_state'=>'',
            'mailing_postal_code'=>'',
            'mailing_country'=>'',
            'practice_address'=>'',
            'practice_address2'=>'',
            'practice_city'=>'',
            'practice_state'=>'',
            'practice_postal_code'=>'',
            'practice_country_code'=>'',
            'telephone'=>'',
            'website'=>'',
            'email'=>'',
            'fax'=>'',
            'codes.code'=>'',
            'codes.classification'=>'',
            'codes.specialization'=>'',
            'circulation_file_last_modified'=>'',
            'hubspot_last_modified'=>'',
            'nppes_last_modified'=>'',
            'etldate'=>''
                ];



        if(!is_array($nppes) || !count($nppes) || !isset($nppes['npi'])) {
        $nppes=[];
        $nppes['npi'] = (string)$row['npi'];
        $nppes['prefix'] = '';
        $nppes['first_name'] = $row['first_name'];
        $nppes['middle_name'] =  '';
        $nppes['last_name'] = $row['last_name'] ;
        $nppes['suffix'] ='';
        $nppes['credentials'] = '';
        $nppes['last_activity_date'] = $row['last_activity_date'];
        $nppes['hubspot_last_modified'] = $row['last_modified_date'];
        $nppes['nppes_last_modified'] =false;
        $nppes['addresses'][]=[
            'type'=>'mailing',
            'first_line'=>$row['street_address'],
            'second_line'=>$row['street_address_2'],
            'city'=>$row['city'],
            'state'=>$row['state_region'],
            'postal_code'=>$row['postal_code'],
            'credentials'=>$row['job_title'],
        ];
        $updateMode='insert';
    }else {
        $updateMode = 'upsert';
    }
        $outfile['npi'] = $nppes['npi'];
        $outfile['prefix'] = $nppes['prefix'] ?? '';
        $outfile['first_name'] = $nppes['first_name'] ?? $nppes['hubspot']['first_name'];
        $outfile['middle_name'] = $nppes['middle_name'] ?? '';
        $outfile['last_name'] = $nppes['last_name'] ?? $nppes['hubspot']['last_name'];
        $outfile['suffix'] = $nppes['suffix'] ?? '';
        $outfile['creds'] = $nppes['credentials'] ?? $row['job_title'];
        $outfile['circulation_file_last_modified'] = $row['last_activity_date'];
        $outfile['hubspot_last_modified'] = $row['last_modified_date'];

        $outfile['nppes_last_modified'] = $nppes['last_update_date']??'1970-01-01 12:00:00';
        foreach ($nppes['addresses'] as $address) {
            if ($address['type'] == 'physical') {
                $outfile['physical_address'] = $address['first_line'];
                $outfile['physical_address2'] = $address['second_line'];
                $outfile['physical_city'] = $address['city'];
                $outfile['physical_state'] = $address['state'];
                $outfile['physical_postal_code'] = $address['postal_code'];
                $outfile['physical_country'] = $address['country']??'';
            } elseif ($address['type'] == 'mailing') {
                $outfile['mailing_address'] = $address['first_line'];
                $outfile['mailing_address2'] = $address['second_line'];
                $outfile['mailing_city'] = $address['city'];
                $outfile['mailing_state'] = $address['state'];
                $outfile['mailing_postal_code'] = $address['postal_code'];
                $outfile['mailing_country'] = $address['country']??'';
            }
        }
       foreach ($nppes['phone_numbers']??[] as $phone) {
            if ($phone['type'] == 'phone') {
                $outfile['telephone'] = $phone['number'];
            } elseif ($phone['type'] == 'fax') {
                $outfile['fax'] = $phone['number'];
            }

        }
        foreach ($nppes['specialties']??[] as $specialty) {
            if ($specialty['is_primary']) {
                $outfile['codes.code'] = $specialty['taxonomy_code'];
                $outfile['codes.classification'] = $specialty['classification'];
                $outfile['codes.specialization'] = $specialty['specialization'];
                break;
            }
        }
        if ($this->lytics_headers_written['nppes'] == false) {
            fputcsv($this->lytics_files['nppes'],array_keys($outfile));
            $this->lytics_headers_written['nppes'] = true;
        }
        fputcsv($this->lytics_files['nppes'], $outfile);


        $hubspot = ['hubspot' => [], 'publications' => []];

        foreach ($this->nppes_map as $find => $field) {
            if (trim($row[$find])) {
                $hubspot['hubspot'][$field] = $row[$find];
            }
        }
        foreach ($this->nppes_magazine_map as $magazine => $map) {
            $temp = [];
            $temp['name'] = $magazine;
            foreach ($map as $find => $field) {
                if (trim($row[$find])) {
                    $temp[$field] = $row[$find];
                }
            }
            if (count($temp) > 1) {

                $hubspot['publications'][$magazine] = $temp;
            }
        }

        $client = (new MongoDB\Client(MONGODB, array(
            'ssl' => true,
            'sslAllowInvalidCertificates' => true
        )))->base->nppes;
        if($updateMode=='upsert') {

            $updateResult = $client->updateMany(
                ['npi' => (string)$row['npi']],
                ['$set' => $hubspot]
            );
        }else{
            $nppes=array_merge($nppes,$hubspot);
            $updateResult = $client->insertOne(
              $nppes
            );
        }

        $this->rows_updated['nppes']++;

    }

    /**
     * @return string
     */
    protected function processFileUpload(){
        file_put_contents('/var/log/pheanstalk.log', __FILE__ . ":" . __LINE__ ." Processing! ".$_FILES["data_merge"]["name"]. "\r\n", FILE_APPEND);
        if ($_FILES["data_merge"]["error"]) {
            $payload = ['message'=>'Error in upload','target'=>'log','type'=>'append','tube'=>$this->tube];
            $this->pheanstalk
                ->useTube($this->tube)
                ->put(json_encode($payload));
            file_put_contents('/var/log/pheanstalk.log', __FILE__ . ":" . __LINE__ ." ERROR! ".$_FILES["data_merge"]["error"]. "\r\n", FILE_APPEND);
            $this->_result['error']=$_FILES["data_merge"]["error"];
            exit;
        }
        $tmp_name = $_FILES["data_merge"]["tmp_name"];
        $name = basename($_FILES["data_merge"]["name"]);
        $zip = new ZipArchive;

        $res = $zip->open($tmp_name);
        if ($res === TRUE) {
            $zip->extractTo('/export');
            $zip->close();

            $payload = ['message'=>'File unzipped','target'=>'log','type'=>'append','tube'=>$this->tube];
            $this->pheanstalk
                ->useTube($this->tube)
                ->put(json_encode($payload));

            file_put_contents('/var/log/pheanstalk.log', __FILE__ . ":" . __LINE__ ."File Unzipped ".$name. "\r\n", FILE_APPEND);
            return "/export/export-for-qorpus.csv";
        } else{
            $payload = ['message'=>'File unzip falied','target'=>'log','type'=>'append','tube'=>$this->tube];
            $this->pheanstalk
                ->useTube($this->tube)
                ->put(json_encode($payload));
            file_put_contents('/var/log/pheanstalk.log', __FILE__ . ":" . __LINE__ ."File Unzip FAILED ".$name. "\r\n", FILE_APPEND);
            exit;
        }
    }

    /**
     * 
     */
    protected function prepLyticsFiles(){
        $list=glob('/export/nppes_*.csv');
        $list2=glob('/export/hubspot_*.csv');
        $old_files=array_merge($list,$list2);
        foreach($old_files as $file){
            unlink($file);
        }
        $this->lytics_fileNames=['nppes'=>sprintf('nppes_%s.csv', $this->start),'hubspot'=>sprintf('hubspot_%s.csv', $this->start)];
        $this->lytics_files['nppes']=fopen(sprintf('/export/%s', $this->lytics_fileNames['nppes']),'w+');
        $this->lytics_files['hubspot']=fopen(sprintf('/export/%s', $this->lytics_fileNames['hubspot']),'w+');
    }

    /**
     *
     */
    protected function process(){
        $this->_format='json';
        $this->tube=$_REQUEST['tube'];
        $payload = ['message'=>'Started Process','target'=>'log','type'=>'append','tube'=>$this->tube];
        $this->pheanstalk
            ->useTube($this->tube)
            ->put(json_encode($payload));
        file_put_contents('/var/log/pheanstalk.log', "New Request:".date("Y-m-d H:i:s") . " - Job: $this->tube  \r\n",FILE_APPEND);
        $this->prepLyticsFiles();

        $data_source='/export/export-for-qorpus.csv';
        if(!file_exists($data_source='/export/export-for-qorpus.csv')){
            $this->_result=['error'=>$data_source." does not exist"];
        }

        $this->total = $this->wc($data_source);
        live_flush("Total $this->total", 'total');
        $fi = fopen($data_source,'r');
        fgetcsv($fi);
        $indexCounter=0;
        while(!feof($fi)){
            $indexCounter++;

            $row=fgetcsv($fi);
            if(is_array($row) && count($row)){


                $this->upsertHubspot($row);
            }
        }

        fclose($this->lytics_files['nppes']);
        $this->removeFromS3('nppes');
        $this->uploadFiles(sprintf("/export/%s", $this->lytics_fileNames['hubspot']), $this->lytics_fileNames['nppes']);

        $params=[
            ':job'=>'export_nppes',
            ':direction'=>'export',
            ':datecreated'=>date("Y-m-d H:i:s",$this->start),
            ':elapsed'=>gmdate("H:i:s",strtotime('now')-$this->start),
            ':records_count'=>$this->wc(sprintf("/export/%s", $this->lytics_fileNames['nppes'])),
            ':status'=>200,
            ':message'=>'',
        ];
        $this->mongoInsert('etl_logs',$params);

        fclose($this->lytics_files['hubspot']);
        $this->removeFromS3('hubspot');
        $this->uploadFiles(sprintf("/export/%s", $this->lytics_fileNames['hubspot']), $this->lytics_fileNames['hubspot']);
        $params=[
            ':job'=>'export_hubspot',
            ':direction'=>'export',
            ':datecreated'=>date("Y-m-d H:i:s",$this->start),
            ':elapsed'=>gmdate("H:i:s",strtotime('now')-$this->start),
            ':records_count'=>$this->wc(sprintf("/export/%s", $this->lytics_fileNames['hubspot'])),
            ':status'=>200,
            ':message'=>'',
        ];
        $this->mongoInsert('etl_logs',$params);

        $params=[
            ':job'=>'import_hubspot',
            ':direction'=>'import',
            ':datecreated'=>date("Y-m-d H:i:s",$this->start),
            ':elapsed'=>gmdate("H:i:s",strtotime('now')-$this->start),
            ':records_count'=>$this->total,
            ':status'=>200,
            ':message'=>'',
        ];
        $this->mongoInsert('etl_logs',$params);
        $payload = ['message'=>'Performing Mongo upsert','target'=>'log','type'=>'append','tube'=>$this->tube];
        $this->pheanstalk
            ->useTube($this->tube)
            ->put(json_encode($payload));
        $this->mongoimport('base','hubspot',sprintf("/export/%s", $this->lytics_fileNames['hubspot']),'csv','upsert',false,'contact_id');


        $payload = ['message'=>'All Doned','target'=>'log','type'=>'terminate','tube'=>$this->tube];
        $this->pheanstalk
            ->useTube($this->tube)
            ->put(json_encode($payload));
        $this->_result=$payload;

    }
    protected function upload()
    {
        $this->tube = "job_" . $this->start;
        $this->_result['tube']=$this->tube;


        file_put_contents('/var/log/pheanstalk.log', __FILE__ . ":" . __LINE__ . " START!\r\n");
        file_put_contents('/var/log/pheanstalk.log', __FILE__ . ":" . __LINE__ . "\r\n", FILE_APPEND);
        if(!file_exists('/export/export-for-qorpus.csv') || filemtime('/export/export-for-qorpus.csv') < strtotime('-1 hour') ){
            file_put_contents('/var/log/pheanstalk.log', __FILE__ . ":" . __LINE__ . "\r\n", FILE_APPEND);
            if(file_exists('/export/export-for-qorpus.csv')){
                file_put_contents('/var/log/pheanstalk.log', __FILE__ . ":" . __LINE__ . "\r\n", FILE_APPEND);
                unlink('/export/export-for-qorpus.csv');
            }
           $data_source =$this->processFileUpload();
        }else{
            $data_source='/export/export-for-qorpus.csv';
        }

        live_tube('/hubspot_etl/hubspot',$this->tube);




    }
    protected function removeFromS3($path)
    {
        $s3Client = S3Client::factory(array(
            'credentials' => array(
                'key' => AWS_ACCESS_KEY_ID_LYTICS,
                'secret' => AWS_SECRET_ACCESS_KEY_LYTICS,
            ),
            'region' => 'us-west-2',
            'version' => 'latest'
        ));
        try {
            $results = $s3Client->getPaginator('ListObjects', [
                'Bucket' => AWS_BUCKET_LYTICS
            ]);
            if(is_array($results) && count($results)) {
                foreach ($results as $idx => $result) {
                    foreach ($result['Contents'] as $idx2 => $object) {
                        $key = $object['Key'];
                        if (strstr($key, ($path . "_")) && strstr($key, (".csv"))) {
                            live_append("Removing old file $key<br/>");
                            $s3Client->deleteObject(array(
                                'Bucket' => AWS_BUCKET_LYTICS,
                                'Key' => $key));
                        }
                    }
                }
            }
        } catch (S3Exception $e) {
            pretty_print_r($e);
        }
    }
    protected function view()
    {
        file_put_contents('/var/log/pheanstalk.log', __FILE__ . ":" . __LINE__ . "\r\n", FILE_APPEND);
        $table = 'hubspot';
        $action = $_REQUEST['action'] ?? 'default';
        switch($action){
            case 'upload':
                file_put_contents('/var/log/pheanstalk.log', __FILE__ . ":" . __LINE__ . "\r\n", FILE_APPEND);
                $this->upload();
                break;
            case 'process':
                $this->process();
                break;
            default:
                file_put_contents('/var/log/pheanstalk.log', __FILE__ . ":" . __LINE__ . "\r\n", FILE_APPEND);
                $this->_result['error'] = false;
                $this->_result['versions'] = $this->mongoQuery('etl_logs', ['job' => ['$eq' => 'export_hubspot'], 'direction' => ['$eq' => 'export']], ['limit' => 20, 'sort' => ['datecreated' => -1], 'projection' => ['_id' => false, 'job' => 1, 'records_count' => 1, 'datecreated' => 1]]);
                $this->_format = 'html';
                $this->_view = 'hubspot_etl/hubspot_merge';
                break;
        }
    }


}

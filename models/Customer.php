<?php

namespace app\models;

use common\components\soap\GcsComponent;
use common\components\soap\RamComponent;
use common\helpers\ArrayHelper;
use common\helpers\Pdf;
use common\models\behaviors\CustomerReminderBehavior;
use common\models\behaviors\HistoryBehavior;
use common\models\behaviors\LogBehavior;
use common\models\behaviors\UpdateBehavior;
use common\models\behaviors\UpdatePortalBehavior;
use common\models\calculators\AmericorCalculator;
use common\models\calculators\CreditCardCalculator;
use common\models\calculators\FrozenLoanCalculator;
use common\models\calculators\PayoffQuoteCalculator;
use common\models\calculators\TotalCalculator;
use common\models\query\CustomerCreditorQuery;
use common\models\query\CustomerQuery;
use common\models\query\CustomerTokenQuery;
use common\models\query\DraftQuery;
use common\models\totals\AdvancesAndRecoupsTotal;
use common\models\totals\AttorneyPaymentsTotal;
use common\models\totals\BankFeeAttorneyPaymentsTotal;
use common\models\totals\BankFeeCreditorPaymentsTotal;
use common\models\totals\BankFeeDraftsTotal;
use common\models\totals\CreditorPaymentsTotal;
use common\models\totals\CustomerCreditorsTotal;
use common\models\totals\DraftsTotal;
use common\models\totals\LoanDraftsTotal;
use common\models\totals\SettlementFeesTotal;
use common\models\traits\ErrorsTrait;
use common\models\behaviors\FormattedBehavior;
use common\models\traits\FormNameTrait;
use common\models\traits\RelationWithTrait;
use common\modules\form\models\behaviors\FormFieldBehavior;
use common\modules\form\models\FormField;
use common\modules\trickle\models\CustomerStarred;
use common\modules\trickle\query\CustomerStarredQuery;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Url;

use common\models\calculators\LoanCalculator;
use common\modules\trickle\models\TrickleSystem;


/**
 * This is the model class for table "{{%customer}}".
 *
 * @property integer $id
 * @property string $unsecured_debt
 * @property string $source
 * @property integer $status
 * @property integer $stage
 * @property @deprecated string $assigned
 * @property string $callback
 * @property integer $co_applicant_id
 * @property string $initial_payment_amount
 * @property string $date_of_initial_payment
 * @property integer $day_of_months_for_subsequent_payments
 * @property string $start_date_for_subsequent_payments
 * @property string $funds_available
 * @property string $income_from_w2
 * @property string $hardship_reason
 * @property string $detailed_hardship_reason
 * @property integer $number_of_months_struggling_with_debt
 * @property string $type
 * @property integer $primary_applicant_id
 * @property string $quality
 * @property integer $co_applicant_enabled
 * @property integer $cancellation_reason
 * @property integer $negotiator_id
 * @property integer $jr_negotiator_id
 * @property integer $sales_rep_id
 * @property integer $opener_id
 * @property integer $client_service_id
 * @property integer $sales_manager_id
 * @property integer $loc_processor_id
 * @property integer $underwriter_id
 * @property string $largest_credit_card_rate
 * @property integer $largest_customer_creditor_id
 * @property integer $number_of_months
 * @property integer $schedule_payments_type
 * @property string $any_down_payment
 * @property float $maximum_settlement_to_creditors
 * @property float $program_percentage
 * @property float $escrow_balance
 * @property integer $file_status
 * @property integer $collection_recovery_file_status
 * @property integer $payment_status
 * @property string $enrolled_date
 * @property string $status_date
 * @property double $average_interest_rate
 * @property double $last_month_payment
 * @property double $monthly_payment_within_budget
 * @property integer $terms_years
 * @property string $debttrakker_lead_id
 * @property string $debttrakker_vendor_id
 * @property string $americorfunding_client_id
 * @property string $next_draft
 * @property string $total_debt
 * @property string $enrolled_total_debt
 * @property string $closed_total_debt
 * @property string $total_interest_rate
 * @property integer $reject_reason
 * @property integer $nurture_reason
 * @property @deprecated string $duplicate_detail
 * @property integer $docs_sent_locked
 * @property integer $is_closed
 * @property string $closed_date
 * @property integer $debttrakker_client_id
 * @property integer $pre_auth @deprecated
 * @property string $ins_ts
 * @property integer $automation_state
 * @property integer $loan_status
 * @property integer $loan_is_signed
 * @property string $third_payment_date
 * @property string $nsf_date
 * @property string $cancelled_date
 * @property integer $dt_id
 * @property integer $from_dm
 * @property string $file_status_date
 * @property string $negotiator_assign_dt
 * @property string $jr_negotiator_assign_dt
 * @property string $missed_call_sms_last_send_date
 * @property string $defaulted_status
 *
 * @property string $loan_available_date
 * @property float $loan_amount
 * @property integer $loan_available_in
 * @property float $loan_interest_rate
 * @property integer $loan_payment_interval
 * @property integer $loan_term
 * @property float $loan_months
 * @property float $loan_payment
 * @property float $loan_total_interest
 * @property float $loan_total_paid
 * @property integer $callback_callschedule_status
 * @property string $callback_callschedule_status_ts
 * @property integer $callscheduler_scheduled
 * @property integer $callscheduler_connected
 * @property integer $automation_sr_ai_state
 * @property string $automation_sr_ai_dt
 * @property string $automation_sr_ai_last_action
 * @property string $automation_sr_ai_last_action_ts
 *
 * @property integer $ach_is_non_primary_applicant
 * @property integer $is_fax_enrollment
 * @property integer $payment_changes
 * @property integer $need_move_funds
 * @property integer $need_cancelled_payments
 *
 * @property float $override_rate
 * @property float $override_finance_charge_percent
 * @property float $override_funding_date
 *
 * @property float $loan_funding_amount
 * @property string $actual_funding_date
 * @property string $freeze_funding_date
 * @property string $freeze_loan_matured_date
 * @property float $freeze_loan_interest_rate
 * @property integer $freeze_loan_payment_interval
 * @property integer $freeze_loan_term
 * @property float $freeze_loan_payment
 * @property float $freeze_loan_total_interest
 * @property float $freeze_loan_total_amount_paid
 * @property float $freeze_loan_balance_due_on_debt_settled_scheduled
 * @property float $freeze_loan_balance_due_on_debt_unsettled_scheduled_percent
 * @property float $freeze_loan_fee_amount_due_on_debt_settled_scheduled
 * @property float $freeze_loan_fee_amount_due_on_debt_unsettled_scheduled_percent
 * @property float $freeze_loan_last_ram_monthly_fee
 * @property float $freeze_loan_last_ram_settlement_fees
 * @property float $freeze_loan_ram_settlement_fees_in_process
 * @property float $freeze_loan_expected_drafts_before_maturity
 * @property float $freeze_loan_current_ram_balance
 * @property float $freeze_loan_current_ram_pending_balance
 * @property float $freeze_loan_finance_charge
 * @property float $freeze_loan_annual_percentage_rate
 * @property float $freeze_loan_balance_due_unsettled_settlement_cushion_percent
 * @property float $freeze_balance_due_americor_recoup
 * @property float $freeze_balance_due_attorney_fees
 * @property float $freeze_summons_cushion
 * @property string $final_loan_signed_dt
 * @property string $current_matured_date
 * @property string $loan_pre_available_date
 * @property string $current_loan_term
 * @property string $current_loan_term_months
 * @property string $current_loan_effective_rate
 * @property string $current_loan_first_payment_date
 * @property string $current_loan_principal
 * @property string $collection_recovery_status
 * @property integer $half_payment
 * @property integer $ytel_id
 *
 * magic
 * @property string $name
 * @property string $firstName
 * @property string $lastName
 * @property string $coName
 * @property string $names
 * @property string $email
 * @property string $coEmail
 * @property string $emails
 * @property string $url
 * @property string $age
 * @property boolean $isDeal
 * @property boolean $isLoan
 * @property boolean $isLead
 * @property string $statusText
 *
 * @property integer $count_emails
 * @property integer $count_sms
 * @property integer $count_calls
 * @property integer $count_creditors
 * @property integer $count_settled_creditors
 * @property array $applicantsEmailList
 * @property string $description4Log
 * @property string $desc4Log
 * @property integer $has_missed_call
 * @property integer $has_new_sms
 * @property integer $has_2nd_day_call
 * @property string $sourceWithCampaign
 * @property array $informationLabels
 * @property string $primaryApplicantPhone
 * @property array $statusClass
 *
 * @property integer $ach_applicant_id
 * @property Applicant $achApplicant
 *
 * @property AppMessage[] $appMessages
 * @property BudgetExpenses[] $budgetExpenses
 * @property FormField[] $formFields
 * @property Call[] $calls
 * @property Call[] $successCalls
 * @property User $clientService
 * @property CoApplicant $coApplicant
 * @property User $negotiator
 * @property User $jrNegotiator
 * @property User $salesManager
 * @property Applicant $primaryApplicant
 * @property User $salesRep
 * @property User $opener
 * @property User $locProcessor
 * @property User $underwriter
 * @property CustomerCreditor[] $customerCreditors
 * @property Data[] $datas
 * @property Document[] $documents
 * @property Document[] $reviewDocuments
 * @property Draft[] $drafts
 * @property Draft $firstDraft
 * @property EmailCustomer[] $emailCustomers
 * @property Note[] $notes
 * @property PlanHistory[] $planHistories
 * @property Ram $ram
 * @property Gcs $gcs
 * @property Sms[] $sms
 * @property Task[] $tasks
 * @property PlanHistory $planCurrent
 * @property PlanHistory $planOriginal
 * @property PlanHistory $planHistory
 *
 * @property LoanPlanHistory $loanPlanCurrent
 * @property LoanPlanHistory $loanPlanOriginal
 * @property LoanPlanHistory $loanPlanRefinanced
 * @property LoanPlanHistory $loanPlanHistory
 * @property LoanPlanHistory[] $loanPlanHistories
 * @property LoanPlanHistory[] $refinancedLoanPlanHistories
 * @property integer $plan_history_id
 * @property integer $loan_plan_history_id
 *
 * @property boolean $isReadonly
 *
 * @property double $total_monthly_income
 * @property string $total_monthly_expenses_including_program_cost
 * @property double $total_monthly_expenses
 * @property double $calculated_funds_available
 * @property string $monthly_debt_to_income_ratio
 * @property string $schedulePaymentsTypeText
 * @property string $typeText
 * @property string $campaign
 * @property integer $dashboard_done
 * @property integer $credit9_reg
 *
 * @property integer $count_not_settled_creditors
 * @property integer $override_exception_loan
 *
 * @property CustomerCreditor[] $activeCustomerCreditors
 * @property CustomerCreditor[] $notActiveCustomerCreditors
 * @property CustomerCreditor[] $notClosedActiveCustomerCreditors
 * @property CustomerCreditor[] $summonsActiveCustomerCreditors
 * @property CustomerCreditor[] $completedCustomerCreditors
 * @property History[] $history
 * @property Data $data
 *
 *
 * @property LoanCalculator $loanCalculator
 * @property LoanCalculator $currentLoanCalculator
 * @property LoanCalculator $actualLoanCalculator
 * @property PayoffQuoteCalculator $payoffQuoteCalculator
 * @property CreditCardCalculator $totalCalculator
 * @property CreditCardCalculator $totalCalculatorDefaultPercent
 * @property AmericorCalculator $americorCalculator
 *
 * @property RamComponent $ramComponent
 * @property RamComponent|GcsComponent $accountManagement
 * @property Document $lastAgreement
 * @property Document $previousAgreement
 *
 * @property PlaidAccounts[] $plaidAccounts
 * @property PlaidIncome $plaidIncome
 * @property PlaidTransaction[] $plaidTransactions
 * @property DealCustomerProperty $dealCustomerProperty
 * @property DealCustomerProperty $dealCustomerPropertyModel
 *
 * @property CustomerCreditorsTotal $customerCreditorsTotal
 * @property LoanDraftsTotal $loanDraftsTotal
 * @property DraftsTotal $draftsTotal
 * @property BankFeeDraftsTotal $bankFeeDraftsTotal
 * @property CreditorPaymentsTotal $creditorPaymentsTotal
 * @property BankFeeCreditorPaymentsTotal $bankFeeCreditorPaymentsTotal
 * @property AttorneyPaymentsTotal $attorneyPaymentsTotal
 * @property BankFeeAttorneyPaymentsTotal $bankFeeAttorneyPaymentsTotal
 * @property SettlementFeesTotal $settlementFeesTotal
 * @property AdvancesAndRecoupsTotal $advancesAndRecoupsTotal
 * @property LoanDraft[] $loanDrafts
 * @property History $historyLastChangeStatus
 * @property CustomerAnswer[] $customerAnswers
 * @property CustomerAnswer $customerAnswerMilitary
 * @property CustomerAnswer $customerAnswerSecurityClearance
 * @property TrickleSystem $trickleSystemActive
 * @property CustomerStarred $customerStarred
 * @property CustomerStarred $customerStarredCurrentUser
 *
 * @property float $achFirstDraftAmount
 * @property boolean $is_enabled_sms
 * @property boolean $is_enabled_contacts
 *
 * @property CustomerToken[] $tokens
 */
class Customer extends \yii\db\ActiveRecord
{
    const STEP_DAY = 'day';
    const STEP_WEEK = 'week';
    const STEP_MONTH = 'month';

    // lead
    const STATUS_LEAD_NEW = 0;
    const STATUS_CALLBACK = 1;

    /** @deprecated */
    const STATUS_ATTEMPTING_CONTACT = 2;

    const STATUS_NURTURED = 3;
    const STATUS_DOCS_SENT = 13;
    const STATUS_OVERDUE = 15;
    const STATUS_READY_TO_PITCH = 16;
    const STATUS_READY_TO_PITCH_NOT_SHOW = 17;
    const STATUS_LEAD_HOT = 18;
    const STATUS_AUTOMATION = 22;
    /** @deprecated */
    const STATUS_SET_TO_CLOSE = 23; // removed
    const STATUS_MAIL_FAX_DOCS = 25;
    /** @deprecated */
    const STATUS_DOCS_SENT_DEAL = 28; // the import temporary status

    /** @deprecated */
    const STATUS_SECOND_VOICE = 14;
    /** @deprecated */
    const STATUS_RE_WORK = 24;
    /** @deprecated */
    const STATUS_2ND_CALL_READY_TO_PITCH = 26;
    /** @deprecated */
    const STATUS_3RD_CALL_READY_TO_PITCH = 27;

    /** @deprecated */
    const STATUS_SCHEDULED_AUTOMATION = 30;
    /** @deprecated */
    const STATUS_AUTOMATION_COMPLETE = 31;
    /** @deprecated */
    const STATUS_AUTOMATION_UNSUCCESSFUL = 32;
    /** @deprecated */
    const STATUS_CANCELLED_AUTOMATION = 33;

    const STATUS_AUTOMATION_SR_AI = 34;
    const STATUS_FOLLOW_UP_PITCH = 41;

    /** @deprecated */
    const STATUS_NOT_ENOUGH_FUNDS = 42; // removed


    // deal
    const STATUS_DEAL_NEW = 4;
    const STATUS_WAITING_FIRST_PAYMENT = 5;
    const STATUS_ACTIVE = 6;
    const STATUS_NSF = 7;
    const STATUS_NSF_UNRESPONSIVE = 8;
    const STATUS_SUSPENDED = 9;
    const STATUS_PENDING_CANCELLATION = 10;
    const STATUS_CANCELLED = 11;
    const STATUS_COMPLETED = 12;
    //const STATUS_PAYMENT_ADJUSTMENT = 17;
    const STATUS_ACH_WAITING_FOR_ESIGN = 20;
    const STATUS_RAM_REJECTED = 21;
    const STATUS_COMPLIANCE_HOLD = 29;
    const STATUS_DEFAULTED = 30;

    const STATUS_PROMISE_TO_PAY = 31;

    const STATUS_COLLECTION_RECOVERY = 35;

    // loan
    /** @deprecated  */
    const STATUS_LOAN_ACTIVE = 40;


    const STATUSES_CLOSED = [
        self::STATUS_CANCELLED,
        self::STATUS_PENDING_CANCELLATION,
        self::STATUS_COMPLETED
    ];

    const FILE_STATUS_NEW = 11;
    const FILE_STATUS_COMPLIANCE_CALL_COMPLETED = 12;
    const FILE_STATUS_COMPLIANCE_CALL_NOT_COMPLETED = 29;
    const FILE_STATUS_COMPLIANCE_CALL_ATTEMPT = 1;
    const FILE_STATUS_COMPLIANCE_CALL_NEEDED = 28;
    const FILE_STATUS_1_WC_ATTEMPT = 2;
    const FILE_STATUS_2_WC_ATTEMPT = 3;
    const FILE_STATUS_3_WC_ATTEMPT = 4;
    const FILE_STATUS_4_WC_ATTEMPT = 5;
    const FILE_STATUS_5_WC_ATTEMPT = 6;
    const FILE_STATUS_6_WC_ATTEMPT = 7;
    const FILE_STATUS_WELCOME_PACK_CALL_COMPLETED = 8;
    const FILE_STATUS_30_DAY_ATTEMPT_1 = 9;
    const FILE_STATUS_30_DAY_ATTEMPT_2 = 10;
    const FILE_STATUS_30_DAY_ATTEMPT_3 = 13;
    const FILE_STATUS_30_DAY_COMPLETED = 14;
    const FILE_STATUS_60_DAY_CALL_ATTEMPTED = 15;
    const FILE_STATUS_60_DAY_CALL_ATTEMPTED_2 = 16;
    const FILE_STATUS_60_DAY_CALL_ATTEMPTED_3 = 17;
    const FILE_STATUS_60_DAY_CALL_ATTEMPTED_4 = 18;
    const FILE_STATUS_60_DAY_CALL_ATTEMPTED_5 = 19;
    const FILE_STATUS_60_DAY_STRATEGY_CALL_COMPLETED = 20;

    const FILE_STATUS_90_DAY_CALL_ATTEMPTED = 21;
    const FILE_STATUS_90_DAY_STRATEGY_CALL_COMPLETED = 22;
    const FILE_STATUS_CANCELLED_BEFORE_WCC = 23;
    const FILE_STATUS_CANCELLED_DURING_WC = 24;
    const FILE_STATUS_COMPLETED_PROGRAM = 25;
    const FILE_STATUS_PROGRAM_REVIEW_CALL_COMPLETED = 26;
    const FILE_STATUS_WELCOME_PACK_CALL_ATTEMPTED = 27;

    //Collection Recovery
    const FILE_STATUS_RECOVERY_REVIEW = 30;
    const FILE_STATUS_COLLECTABLE = 31;
    const FILE_STATUS_COLLECTION_LETTER_SENT = 32;
    const FILE_STATUS_COLLECTION_CALL_1 = 33;
    const FILE_STATUS_COLLECTION_LETTER_2 = 34;
    const FILE_STATUS_COLLECTION_CALL_2 = 35;
    const FILE_STATUS_COLLECTION_LETTER_3 = 36;
    const FILE_STATUS_COLLECTION_CALL_3 = 37;
    const FILE_STATUS_COLLECTION_LETTER_4_FINAL = 38;
    const FILE_STATUS_COLLECTION_CALL_4 = 39;
    const FILE_STATUS_WRITE_OFF = 40;
    const FILE_STATUS_SEND_TO_SMALL_CLAIMS = 41;
    const FILE_STATUS_INSTALLMENT_PLAN_SET = 42;
    const FILE_STATUS_PAID_IN_FULL = 43;

    const FILE_STATUS_WC_AUTO_SMS_EMAIL = 44;
    const FILE_STATUS_WC_AUTO_SCHEDULED = 45;
    const FILE_STATUS_WC_AUTO_SUCCESS = 46;
    const FILE_STATUS_WC_AUTO_NOT_SUCCESS = 47;
    const FILE_STATUS_WC_AUTO_CANCELLED = 48;

    const FILE_STATUS_30_DAY_AUTO_SMS_EMAIL = 49;
    const FILE_STATUS_30_DAY_AUTO_SCHEDULED = 50;
    const FILE_STATUS_30_DAY_AUTO_SUCCESS = 51;
    const FILE_STATUS_30_DAY_AUTO_NOT_SUCCESS = 52;
    const FILE_STATUS_30_DAY_AUTO_CANCELLED = 53;

    const FILE_STATUSES_WC_AUTO = [
        self::FILE_STATUS_WC_AUTO_SMS_EMAIL,
        self::FILE_STATUS_WC_AUTO_SCHEDULED,
        self::FILE_STATUS_WC_AUTO_SUCCESS,
        self::FILE_STATUS_WC_AUTO_NOT_SUCCESS,
        self::FILE_STATUS_WC_AUTO_CANCELLED,
    ];

    const FILE_STATUSES_30_DAY = [
        self::FILE_STATUS_30_DAY_AUTO_SMS_EMAIL,
        self::FILE_STATUS_30_DAY_AUTO_SCHEDULED,
        self::FILE_STATUS_30_DAY_AUTO_SUCCESS,
        self::FILE_STATUS_30_DAY_AUTO_NOT_SUCCESS,
        self::FILE_STATUS_30_DAY_AUTO_CANCELLED,
    ];

    const PAYMENT_STATUS_NEW = 1;
    const PAYMENT_STATUS_UNABLE_TO_RETAIN = 2;
    const PAYMENT_STATUS_RETAINED = 3;
    const PAYMENT_STATUS_NSF_ATTEMPT_1 = 4;
    const PAYMENT_STATUS_NSF_ATTEMPT_2 = 5;
    const PAYMENT_STATUS_NSF_ATTEMPT_3 = 6;
    const PAYMENT_STATUS_NSF_ATTEMPT_4 = 7;
    const PAYMENT_STATUS_NSF_ATTEMPT_5 = 8;
    const PAYMENT_STATUS_48_HOURS_TO_CX = 9;
    const PAYMENT_STATUS_24_HOURS_TO_CX = 10;
    const PAYMENT_STATUS_24_HOURS_TO_REACTIVATION = 11;
    const PAYMENT_STATUS_48_HOURS_TO_REACTIVATION = 12;

    const COLLECTION_STATUS_PROMISE_TO_PAY = 'PTP';
    const COLLECTION_STATUS_OUT_TO_RAISE = 'OTR';
    const COLLECTION_STATUS_PARTIAL_PAYMENT_ARRANGEMENT = 'PPA';
    const COLLECTION_STATUS_BROKEN_PROMISE = 'BP';
    const COLLECTION_STATUS_REFUSE_TO_PAY = 'RTP';
    const COLLECTION_STATUS_BANKRUPTCY = 'BKY';
    const COLLECTION_STATUS_DECEASE = 'DEC';
    const COLLECTION_STATUS_RETAINED_AN_ATTORNEY = 'ATTY';
    const COLLECTION_STATUS_SKIP_ACCOUNT = 'SKIP';
    const COLLECTION_STATUS_PAID_IN_FULL = 'PIF';
    const COLLECTION_STATUS_BALANCE_IN_FULL = 'BIF';
    const COLLECTION_STATUS_SETTLED_IN_FULL= 'SIF';

    const TYPE_LEAD = 'lead';
    const TYPE_DEAL = 'deal';
    const TYPE_LOAN = 'loan';

    const QUALITY_ACTIVE = 'active';
    const QUALITY_REJECTED = 'rejected';
    const QUALITY_COMMUNITY = 'community';
    const QUALITY_UNASSIGNED = 'unassigned';
    const QUALITY_TRICKLE = 'trickle';

    const SOURCE_AMERICORFUNDING = 'Americorfunding';
    const SOURCE_AMERICOR_INTAKE = 'Americor Intake';
    const SOURCE_BBB_LEAD = 'BBB LEAD';
    const SOURCE_MAILER = 'MAILER';
    const SOURCE_MAILER_WEBSITE_1 = 'Mailer Website 1';
    const SOURCE_MAILER_WEBSITE_2 = 'Mailer Website 2';
    const SOURCE_MAILER_WEBSITE_4 = 'Mailer Website 4';
    const SOURCE_SEB = 'SEB';
    const SOURCE_TV_CALL = 'TV Call';
    const SOURCE_WEB = 'Web';
    const SOURCE_MANUAL = 'Manual';
    const SOURCE_API = 'API';
    const SOURCE_CREDIT9 = 'Credit9';
    const SOURCE_FASTLOANSFUNDING = 'fastloansfunding';

    const SOURCE_CREDIT_ORG = 'Credit.org';
    const SOURCE_CREDIT_SESAME = 'Credit Sesame';
    const SOURCE_GOOGLE = 'Google';
    const SOURCE_FACEBOOK = 'Facebook';
    const SOURCE_OTHER_DIGITAL = 'Other Digital';
    const SOURCE_REFERRAL = 'Referral';
    const SOURCE_MAILER_NO_CODE = 'Mailer (no code)';
    const SOURCE_OTHER = 'Other';

    const SOURCE_LENDINGTREE = 'lendingtree';

    const SCHEDULE_PAYMENTS_TYPE_MONTHLY = 0;
    const SCHEDULE_PAYMENTS_TYPE_BI_MONTHLY = 1;
    const SCHEDULE_PAYMENTS_TYPE_BI_WEEKLY = 2;

    const APPLICANT_TYPE_INDIVIDUAL = 0;
    const APPLICANT_TYPE_JOINT = 1;

    const SCENARIO_FORM_CHANGE_STATUS = 'form_change_status';
    const SCENARIO_FORM_UPLOAD_DOCS = 'form_upload_docs';
    const SCENARIO_IMPORT = 'import';
    const SCENARIO_UPDATE_WITHOUT_FORMATTED = 'update_without_formatted';
//    const SCENARIO_DEAL_FORM_LOAN_PLAN = 'deal_form_loan_plan';
//    const SCENARIO_LOAN_FORM_LOAN_PLAN = 'loan_form_loan_plan';

    /** @deprecated */
    const HARDSHIP_REASON_NOT_ABLE_TO_SAVE = 0;
    /** @deprecated */
    const HARDSHIP_REASON_HIGH_INTEREST_RATES = 4;
    /** @deprecated */
    const HARDSHIP_REASON_DEBT_TO_INCOME_TO_HIGH = 2;

    const HARDSHIP_REASON_AVOID_BANKRUPTCY = 1;
    const HARDSHIP_REASON_DIVORCED = 3;
    const HARDSHIP_REASON_ILLNESS_IN_FAMILY = 5;
    const HARDSHIP_REASON_LOSS_OF_INCOME = 6;
    const HARDSHIP_REASON_WIDOWED = 7;
    const HARDSHIP_REASON_MEDICAL_ISSUES = 8;
    const HARDSHIP_REASON_OTHER = 9;
    const HARDSHIP_REASON_BIRTH = 10;
    const HARDSHIP_REASON_SPECIAL_NEEDS_FAMILY = 11;
    const HARDSHIP_REASON_LOSS_OF_JOB = 12;
    const HARDSHIP_REASON_LAID_OFF= 13;
    const HARDSHIP_REASON_UNEXPECTED_EXPENSES = 14;

    const LOAN_STATUS_AGREEMENT_ONLY = 0;
    const LOAN_STATUS_LOAN_ONLY = 1;
    const LOAN_STATUS_AGREEMENT_WITH_LOAN = 2;

    const LOAN_IS_SIGNED_NO = 0;
    const LOAN_IS_SIGNED_YES = 1;
    const LOAN_IS_SIGNED_SENT = 2;

    const AUTOMATION_SR_AI_LAST_ACTION__SMS_SENT = 'sms_sent';
    const AUTOMATION_SR_AI_LAST_ACTION__CALLED_CUSTOMER_AND_LEFT_VOICEMAIL = 'called_customer_and_left_voicemail';
    const AUTOMATION_SR_AI_LAST_ACTION__SUCCESS_RECEIVED_AN_EMAIL = 'success_received_an_email';
    const AUTOMATION_SR_AI_LAST_ACTION__CALLED_CUSTOMER_NO_ANSWER = 'called_customer_no_answer';
    const AUTOMATION_SR_AI_LAST_ACTION__SUCCESS_CALL = 'success_call';
    const AUTOMATION_SR_AI_LAST_ACTION__SUCCESS_INBOUND_CALL = 'success_inbound_call';
    const AUTOMATION_SR_AI_LAST_ACTION__INBOUND_CALL_FAIL = 'inbound_call_fail';
    const AUTOMATION_SR_AI_LAST_ACTION__SUCCESS_RECEIVED_AN_SMS = 'success_received_an_sms';
    const AUTOMATION_SR_AI_LAST_ACTION__EMAIL_SENT = 'email_sent';
    const AUTOMATION_SR_AI_LAST_ACTION__SEQUENCE_COMPLETED = 'sequence_completed';

    const INFORMATION_LABEL_MISSED_CALL = 'missed_call';
    const INFORMATION_LABEL_NEW_SMS = 'new_sms';
    const INFORMATION_LABEL_2ND_DAY_CALL = '2nd_day_call';
    const INFORMATION_LABEL_AUTO_REJECT_NO_SCHEDULE_CALL = 'auto_reject_no_schedule_call';

    const CREDIT9_REG_REGISTERED = 1;
    const CREDIT9_REG_NOT_REGISTERED = 0;

    public $formatted_date_of_initial_payment;
    public $formatted_start_date_for_subsequent_payments;
    public $formatted_third_payment_date;

    public $new_payment_day;
    public $new_payment_amount;
    public $new_payment_starting_date;
    public $new_payment_ending_date;

    public $plan_history_id;
    public $loan_plan_history_id;

    private $_customerCreditorsTotal;
    private $_draftsTotal;
    private $_bankFeeDraftsTotal;
    private $_loanDraftsTotals;
    private $_creditorPaymentsTotal;
    private $_bankFeeCreditorPaymentsTotal;
    private $_attorneyPaymentsTotal;
    private $_bankFeeAttorneyPaymentsTotal;
    private $_settlementFeesTotal;
    private $_advancesAndRecoupsTotal;


    private $_totalCalculator;
    private $_totalCalculatorDefaultPercent;
    private $_americorCalculator;
    private $_loanCalculator;
    private $_frozenLoanCalculator;
    private $_currentLoanCalculator;
    private $_actualLoanCalculator;
    private $_payoffQuoteCalculator;

    private $_achApplicant;
    private $_activeCustomerCreditorsWithSettlementCushion;

    private static $_salt = '';

    public $docsUpload;

    public static $_routes = [
        self::TYPE_LEAD => 'leads',
        self::TYPE_DEAL => 'deals',
        self::TYPE_LOAN => 'loans'
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
                [
                    [
                        'funds_available',
                        'income_from_w2',
                        'any_down_payment',
                        'maximum_settlement_to_creditors',
                        'initial_payment_amount',
                        'maximum_settlement_to_creditors',
                        'program_percentage',
                        'largest_credit_card_rate',
                        'total_interest_rate',
                        'average_interest_rate',
                        'last_month_payment',
                        'monthly_payment_within_budget',
                        'override_rate',
                        'override_finance_charge_percent',
                    ],
                    'filter',
                    'filter' => function ($value) {
                        return (float)preg_replace('#[^0-9.]#', '', $value);
                    },
                    'except' => self::SCENARIO_IMPORT
                ],
                [
                    [
                        'unsecured_debt',
                        'total_debt',
                        'initial_payment_amount',
                        'funds_available',
                        'largest_credit_card_rate',
                        'any_down_payment',
                        'maximum_settlement_to_creditors',
                        'program_percentage',
                        'escrow_balance',
                        'average_interest_rate',
                        'last_month_payment',
                        'monthly_payment_within_budget',
                        'total_interest_rate',
                        'enrolled_total_debt',
                        'closed_total_debt',
                        'loan_amount',
                        'loan_interest_rate',
                        'loan_months',
                        'loan_payment',
                        'loan_total_interest',
                        'loan_total_paid',
                        'override_rate',
                        'override_finance_charge_percent',
                        'loan_funding_amount',
                        'freeze_loan_interest_rate',
                        'freeze_loan_payment',
                        'freeze_loan_total_interest',
                        'freeze_loan_total_amount_paid',
                        'freeze_loan_balance_due_on_debt_settled_scheduled',
                        'freeze_loan_balance_due_on_debt_unsettled_scheduled_percent',
                        'freeze_loan_balance_due_unsettled_settlement_cushion_percent',
                        'freeze_loan_fee_amount_due_on_debt_settled_scheduled',
                        'freeze_loan_fee_amount_due_on_debt_unsettled_scheduled_percent',
                        'freeze_loan_last_ram_monthly_fee',
                        'freeze_loan_last_ram_settlement_fees',
                        'freeze_loan_ram_settlement_fees_in_process',
                        'freeze_loan_expected_drafts_before_maturity',
                        'freeze_loan_current_ram_balance',
                        'freeze_loan_current_ram_pending_balance',
                        'freeze_loan_finance_charge',
                        'freeze_loan_annual_percentage_rate',
                        'freeze_balance_due_americor_recoup',
                        'freeze_balance_due_attorney_fees',
                        'freeze_summons_cushion',
                        'current_loan_effective_rate',
                        'current_loan_principal',
                    ],
                    'number'
                ],
                [
                    [
                        'status',
                        'file_status',
                        'collection_recovery_file_status',
                        'payment_status',
                        'stage',
                        'co_applicant_id',
                        'day_of_months_for_subsequent_payments',
                        'number_of_months_struggling_with_debt',
                        'primary_applicant_id',
                        'co_applicant_enabled',
                        'cancellation_reason',
                        'negotiator_id',
                        'sales_rep_id',
                        'client_service_id',
                        'sales_manager_id',
                        'loc_processor_id',
                        'underwriter_id',
                        'largest_customer_creditor_id',
                        'number_of_months',
                        'schedule_payments_type',
                        'terms_years',
                        'nurture_reason',
                        'reject_reason',
                        'docs_sent_locked',
                        'is_closed',
                        'debttrakker_client_id',
                        'automation_state',
                        'loan_status',
                        'loan_is_signed',
                        'dt_id',
                        'from_dm',
                        'jr_negotiator_id',
                        'loan_available_in',
                        'loan_payment_interval',
                        'loan_term',
                        'callback_callschedule_status',
                        'ach_is_non_primary_applicant',
                        'dashboard_done',
                        'callscheduler_scheduled',
                        'callscheduler_connected',
                        'automation_sr_ai_state',
                        'is_fax_enrollment',
                        'payment_changes',
                        'need_move_funds',
                        'need_cancelled_payments',
                        'freeze_loan_payment_interval',
                        'freeze_loan_term',
                        'current_loan_term',
                        'current_loan_term_months',
                        'half_payment',
                        'override_exception_loan',
                        'ytel_id'
                    ],
                    'integer'
                ],
                [
                    [
                        'callback',
                        'enrolled_date',
                        'status_date',
                        'date_of_initial_payment',
                        'start_date_for_subsequent_payments',
                        'next_draft',
                        'closed_date',
                        'ins_ts',
                        'third_payment_date',
                        'nsf_date',
                        'cancelled_date',
                        'file_status_date',
                        'negotiator_assign_dt',
                        'jr_negotiator_assign_dt',
                        'loan_available_date',
                        'callback_callschedule_status_ts',
                        'automation_sr_ai_dt',
                        'automation_sr_ai_last_action_ts',
                        'actual_funding_date',
                        'freeze_funding_date',
                        'freeze_loan_matured_date',
                        'missed_call_sms_last_send_date',
                        'final_loan_signed_dt',
                        'current_matured_date',
                        'loan_pre_available_date',
                        'collection_recovery_status',
                        'current_loan_first_payment_date',
                    ],
                    'safe'
                ],
                [['detailed_hardship_reason', 'duplicate_detail', 'campaign', 'automation_sr_ai_last_action'], 'string'],
                [[
                    'has_missed_call',
                    'has_new_sms',
                    'has_2nd_day_call',
                    'is_enabled_sms',
                    'is_enabled_contacts',
                ], 'boolean'],
                [['primary_applicant_id'], 'required'],
                [['hardship_reason', 'type', 'quality', 'debttrakker_lead_id', 'debttrakker_vendor_id', 'americorfunding_client_id', 'source'], 'string', 'max' => 255],

                [['client_service_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['client_service_id' => 'id']],
                [['co_applicant_id'], 'exist', 'skipOnError' => true, 'targetClass' => Applicant::className(), 'targetAttribute' => ['co_applicant_id' => 'id']],
                [['negotiator_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['negotiator_id' => 'id']],
                [['jr_negotiator_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['jr_negotiator_id' => 'id']],
                [['primary_applicant_id'], 'exist', 'skipOnError' => true, 'targetClass' => Applicant::className(), 'targetAttribute' => ['primary_applicant_id' => 'id']],
                [['sales_rep_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['sales_rep_id' => 'id']],
                [['opener_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['opener_id' => 'id']],
                [['sales_manager_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['sales_manager_id' => 'id']],
                [['loc_processor_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['loc_processor_id' => 'id']],
                [['underwriter_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['underwriter_id' => 'id']],
                // --- formatted validators
                [['plan_history_id', 'loan_plan_history_id'], 'safe'],
                [
                    [
                        'formatted_date_of_initial_payment',
                        'formatted_start_date_for_subsequent_payments',
                        'formatted_third_payment_date'
                    ],
                    'safe',
                    'except' => self::SCENARIO_UPDATE_WITHOUT_FORMATTED
                ],

                [['new_payment_day'], 'integer', 'max' => 30, 'min' => 1],
                [['new_payment_amount'], 'number'],

                ['reject_reason', 'required', 'when' => function (self $model) {
                    return $model->quality == self::QUALITY_REJECTED && $model->type == self::TYPE_LEAD;
                }],
                [
                    'automation_state',
                    'required',
                    'when' => function (self $model) {
                        return $model->status == self::STATUS_AUTOMATION;
                    }
                ],
                [
                    'status',
                    'required',
                    'on' => self::SCENARIO_FORM_CHANGE_STATUS
                ],
                [
                    'status',
                    function ($attribute) {
                        if ($this->isAttributeChanged($attribute, false) && in_array($this->{$attribute}, [
                                self::STATUS_NSF,
                                self::STATUS_DOCS_SENT,
                                self::STATUS_MAIL_FAX_DOCS
                            ])) {
                            $this->addError($attribute, Yii::t('app', 'Invalid status'));
                        }
                    },
                    'on' => self::SCENARIO_FORM_CHANGE_STATUS
                ],
                ['docsUpload', 'file', 'on' => self::SCENARIO_FORM_UPLOAD_DOCS],
            ] + (isset(Yii::$app->user) && Yii::$app->user->can('members.leads.isOpener') ? [
                [
                    '!sales_manager_id',
                ],
                'safe'
            ] : []);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'Client ID'),
            'unsecured_debt' => Yii::t('app', 'Unsecured Debt'),
            'source' => Yii::t('app', 'Source'),
            'sourceText' => Yii::t('app', 'Source'),
            'status' => Yii::t('app', 'Status'),
            'statusText' => Yii::t('app', 'Status'),
            'file_status' => Yii::t('app', 'File Status'),
            'fileStatusText' => Yii::t('app', 'File Status'),
            'stage' => Yii::t('app', 'Stage'),
            'ins_ts' => Yii::t('app', 'Assigned'),
            'callback' => Yii::t('app', 'Callback'),
            'co_applicant_id' => Yii::t('app', 'Co Applicant ID'),
            'mothers_maiden_name' => Yii::t('app', 'Mothers Maiden Name'),
            'number_of_dependents' => Yii::t('app', 'Number Of Dependents'),
            'initial_payment_amount' => Yii::t('app', 'Initial Payment Amount'),
            'date_of_initial_payment' => Yii::t('app', 'Date Of Initial Payment'),
            'formatted_date_of_initial_payment' => Yii::t('app', 'Date Of Initial Payment'),
            'day_of_months_for_subsequent_payments' => Yii::t('app', 'Day Of Months For Subsequent Payments'),
            'start_date_for_subsequent_payments' => Yii::t('app', 'Second Payment (recurring)'),
            'formatted_start_date_for_subsequent_payments' => Yii::t('app', 'Second Payment (recurring)'),
            'funds_available' => Yii::t('app', 'Funds Available'),
            'income_from_w2' => Yii::t('app', 'Income from W2'),
            'hardship_reason' => Yii::t('app', 'Hardship Reason'),
            'hardshipReasonText' => Yii::t('app', 'Hardship Reason'),
            'detailed_hardship_reason' => Yii::t('app', 'Detailed Hardship Reason'),

            'number_of_months_struggling_with_debt' => Yii::t('app', 'Months Struggling With Debt'),
            'type' => Yii::t('app', 'Type'),
            'typeText' => Yii::t('app', 'Type'),
            'primary_applicant_id' => Yii::t('app', 'Primary Applicant ID'),
            'quality' => Yii::t('app', 'Quality'),
            'co_applicant_enabled' => Yii::t('app', 'Co-Applicant'),
            'cancellation_reason' => Yii::t('app', 'Cancellation Reason'),
            'cancellationReasonText' => Yii::t('app', 'Cancellation Reason'),
            'settlement_amount' => Yii::t('app', 'Settlement Amount'),
            'settlement_percent' => Yii::t('app', 'Settlement Percent'),
            'settlement_letter_date' => Yii::t('app', 'Settlement Letter Date'),
            'next_draft' => Yii::t('app', 'Next Draft'),

            'sales_rep_id' => Yii::t('app', 'Sales Rep'),
            'opener_id' => Yii::t('app', 'Opener'),
            'sales_manager_id' => Yii::t('app', 'Sales Manager'),
            'salesRep.fullname' => Yii::t('app', 'Sales Rep'),
            'salesRep.userTeams' => Yii::t('app', 'Team'),
            'negotiator.fullname' => Yii::t('app', 'Negotiator'),
            'opener.fullname' => Yii::t('app', 'Opener'),
            'salesManager.fullname' => Yii::t('app', 'Sales Manager'),
            'clientService.fullname' => Yii::t('app', 'Client Service'),
            'locProcessor.fullname' => Yii::t('app', 'LOC Processor'),
            'loc_processor_id' => Yii::t('app', 'LOC Processor'),
            'underwriter_id' => Yii::t('app', 'Underwriter'),
            'underwriter.fullname' => Yii::t('app', 'Underwriter'),
            'negotiator_id' => Yii::t('app', 'Negotiator'),
            'jr_negotiator_id' => Yii::t('app', 'Jr. Negotiator'),
            'client_service_id' => Yii::t('app', 'Client Service'),


            'largest_credit_card_rate' => Yii::t('app', 'Largest Credit Card Rate'),
            'largest_customer_creditor_id' => Yii::t('app', 'Largest Customer Creditor ID'),
            'number_of_month' => Yii::t('app', 'Number Of Months'),
            'schedule_payments_type' => Yii::t('app', 'Payments'),

            'any_down_payment' => Yii::t('app', 'Initial Payment'),

            'maximum_settlement_to_creditors' => Yii::t('app', 'Maximum Settlement'),
            'program_percentage' => Yii::t('app', 'Program Percentage'),
            'escrow_balance' => Yii::t('app', 'Escrow Balance'),
            'total_interest_rate' => Yii::t('app', 'Interest Rate'),
            'reject_reason' => Yii::t('app', 'Reason'),
            'docs_sent_locked' => Yii::t('app', 'Locked'),
            'override_rate' => Yii::t('app', 'Override Rate'),
            'override_finance_charge_percent' => Yii::t('app', 'Override Finance Charge, %'),
            'loan_is_signed' => Yii::t('app', 'Line of Credit'),
            'third_payment_date' => Yii::t('app', 'Third Payment (recurring)'),
            'formatted_third_payment_date' => Yii::t('app', 'Third Payment (recurring)'),

            'enrolled_date' => Yii::t('app', 'Enrolled Date'),

            // ---
            'count_emails' => Yii::t('app', 'Emails sent'),
            'count_sms' => Yii::t('app', 'SMS sent'),
            'count_calls' => Yii::t('app', 'Calls made'),

            'new_payment_day' => Yii::t('app', 'Payment Day'),
            'draft_starting_date' => Yii::t('app', 'Starting Date'),
            'draft_ending_date' => Yii::t('app', 'Ending Date'),
            'draft_new_payment_amount' => Yii::t('app', 'New Payment Amount'),

            'planCurrent.debt_total' => Yii::t('app', 'Current Plan Debt Total'),
            'planCurrent.months' => Yii::t('app', 'Current Plan Months'),
            'planCurrent.estimated_settlement' => Yii::t('app', 'Estimated Settlement'),
            'planCurrent.estimated_settlement_fee' => Yii::t('app', 'Estimated Settlement Fee'),
            'planCurrent.RAM_fee' => Yii::t('app', 'RAM Fee'),
            'planCurrent.total_saving' => Yii::t('app', 'Total Saving'),
            'planCurrent.nextDraft.date' => Yii::t('app', 'Next Draft'),

            'total_monthly_income' => Yii::t('app', 'Total Monthly Income All Source'),
            'total_monthly_expenses_including_program_cost' => Yii::t('app', 'Total Monthly Expenses Including Program Cost'),
            'calculated_funds_available' => Yii::t('app', 'Funds Available'),
            'monthly_debt_to_income_ratio' => Yii::t('app', 'Monthly debt-to-income ratio'),

            'sourceWithCampaign' => Yii::t('app', 'Campaign'),

            'file_status_date' => Yii::t('app', 'File Status Date'),

            'loanIsSignedText' => Yii::t('app', 'LOC'),

            'loan_amount' => Yii::t('app', 'Loan Amount'),
            'loan_available_in' => Yii::t('app', 'Loan Available in'),
            'loan_available_date' => Yii::t('app', 'Available Date'),
            'loan_interest_rate' => Yii::t('app', 'Interest Rate'),
            'loan_payment_interval' => Yii::t('app', 'Payment Interval'),
            'loanPaymentIntervalText' => Yii::t('app', 'Payment Interval'),
            'loan_term' => Yii::t('app', 'Term'),
            'loan_months' => Yii::t('app', 'Months'),
            'loan_payment' => Yii::t('app', 'Loan Payment'),
            'loan_total_interest' => Yii::t('app', 'Total Interest'),
            'loan_total_paid' => Yii::t('app', 'Total Amount Paid'),

            'ach_is_non_primary_applicant' => Yii::t('app', 'Non-primary Applicant'),
            'payment_status' => Yii::t('app', 'Payment'),
            'paymentStatusText' => Yii::t('app', 'Payment'),

            'callscheduler_scheduled' => Yii::t('app', 'Scheduled'),
            'callscheduler_connected' => Yii::t('app', 'Connected'),
            'automation_sr_ai_state' => Yii::t('app', 'Automation SR.ai state'),

            'payment_changes' => Yii::t('app', 'Payment Changes'),

            'credit9_reg' => Yii::t('app', 'Client Portal Account'),

            'loan_funding_amount' => Yii::t('app', 'Required Loan Amount'),
            'freeze_loan_matured_date' => Yii::t('app', 'Matured'),
            'freeze_loan_interest_rate' => Yii::t('app', 'Interest Rate'),
            'freeze_loan_payment_interval' => Yii::t('app', 'Payment Interval'),
            'freeze_loan_term' => Yii::t('app', 'Term'),
            'freeze_loan_payment' => Yii::t('app', 'Loan payment'),
            'freeze_loan_total_interest' => Yii::t('app', 'Total Interest'),
            'freeze_loan_total_amount_paid' => Yii::t('app', 'Total Amount Paid'),

            'freeze_loan_balance_due_on_debt_settled_scheduled' => Yii::t('app', 'Balance due on Debt Settled (Scheduled)'),
            'freeze_loan_balance_due_on_debt_unsettled_scheduled_percent' => Yii::t('app', 'Balance due on Debt Unsettled'),
            'freeze_loan_balance_due_unsettled_settlement_cushion_percent' => Yii::t('app', 'Balance due - Unsettled Settlement Cushion'),
            'freeze_loan_fee_amount_due_on_debt_settled_scheduled' => Yii::t('app', 'Fee amount due on Debt Settled (Scheduled)'),
            'freeze_loan_fee_amount_due_on_debt_unsettled_scheduled_percent' => Yii::t('app', 'Fee amount due on Debt Unsettled'),
            'freeze_loan_last_ram_monthly_fee' => Yii::t('app', 'Last RAM Monthly Fee'),
            'freeze_loan_last_ram_settlement_fees' => Yii::t('app', 'Last RAM Settlement Fees'),
            'freeze_loan_ram_settlement_fees_in_process' => Yii::t('app', 'RAM Settlement Fees in Process'),
            'freeze_loan_expected_drafts_before_maturity' => Yii::t('app', 'Expected Drafts before Maturity'),
            'freeze_loan_current_ram_balance' => Yii::t('app', 'Current RAM Balance'),
            'freeze_loan_current_ram_pending_balance' => Yii::t('app', 'Current RAM Pending Balance'),
            'freeze_loan_finance_charge' => Yii::t('app', 'Prepaid Finance Charge'),
            'freeze_loan_annual_percentage_rate' => Yii::t('app', 'Annual Percentage Rate'),
            'freeze_balance_due_americor_recoup' => Yii::t('app', 'Balance due - Americor Recoup'),

            'enrolled_total_debt' => Yii::t('app', 'Program Total at Enrollment'),
            'closed_total_debt' => Yii::t('app', 'Program Total at 1st Cleared'),
            'final_loan_signed_dt' => Yii::t('app', 'Signed Date'),

            'name' => Yii::t('app', 'Client Name'),
            'informationLabels' => Yii::t('app', 'Labels'),
            'half_payment' => Yii::t('app', 'Make Initial Payment a Half Payment'),

            'is_enabled_sms' => Yii::t('app', 'SMS disabled'),
            'is_enabled_contacts' => Yii::t('app', 'Do Not Contact'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCalls()
    {
        return $this->hasMany(Call::className(), ['customer_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCoApplicant()
    {
        return $this->hasOne(CoApplicant::className(), ['id' => 'co_applicant_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCoApplicantEnabled()
    {
        return
            $this->co_applicant_enabled
                ? $this->hasOne(CoApplicant::className(), ['id' => 'co_applicant_id'])
                : null;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClientService()
    {
        return $this->hasOne(User::className(), ['id' => 'client_service_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNegotiator()
    {
        return $this->hasOne(User::className(), ['id' => 'negotiator_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getJrNegotiator()
    {
        return $this->hasOne(User::className(), ['id' => 'jr_negotiator_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPrimaryApplicant()
    {
        return $this->hasOne(Applicant::className(), ['id' => 'primary_applicant_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSalesRep()
    {
        return $this->hasOne(User::className(), ['id' => 'sales_rep_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOpener()
    {
        return $this->hasOne(User::className(), ['id' => 'opener_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSalesManager()
    {
        return $this->hasOne(User::className(), ['id' => 'sales_manager_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLocProcessor()
    {
        return $this->hasOne(User::className(), ['id' => 'loc_processor_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUnderwriter()
    {
        return $this->hasOne(User::class, ['id' => 'underwriter_id']);
    }

    /**
     * @return \yii\db\ActiveQuery|CustomerCreditorQuery
     */
    public function getCustomerCreditors()
    {
        return $this->hasMany(CustomerCreditor::className(), ['customer_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery|CustomerCreditorQuery
     */
    public function getLoanDrafts()
    {
        return $this->hasMany(LoanDraft::class, ['customer_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomerAnswers()
    {
        return $this->hasMany(CustomerAnswer::class, ['customer_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomerAnswerMilitary()
    {
        return $this->hasOne(CustomerAnswer::class, ['customer_id' => 'id'])->joinWith('question', false)->andWhere(['customer_question.type' => CustomerQuestion:: TYPE_ACTIVE_MILITARY])->limit(1);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomerAnswerSecurityClearance()
    {
        return $this->hasOne(CustomerAnswer::class, ['customer_id' => 'id'])->joinWith('question', false)->andWhere(['customer_question.type' => CustomerQuestion:: TYPE_SECURITY_CLEARANCE])->limit(1);
    }

    /**
     * @return array
     */
    public function getActiveCustomerCreditorsWithSettlementCushion()
    {
        if (!$this->_activeCustomerCreditorsWithSettlementCushion) {
            $this->_activeCustomerCreditorsWithSettlementCushion = [];
            $fSettlementCushion = false;
            foreach ($this->activeCustomerCreditors as $customerCreditor) {
                if ($customerCreditor->status == CustomerCreditor::STATUS_LOAN_PLACEMENT_WAIT) {
                    $fSettlementCushion = true;
                    $minimum_percentage_to_settle = isset($this->planCurrent->estimated_settlement_percent) ? $this->planCurrent->estimated_settlement_percent : Yii::$app->params['americor']['estimated_settlement_percent'];
                    $balanceDueOnDebt = Yii::$app->formatter->asCurrency(round($customerCreditor->active_current_balance * $minimum_percentage_to_settle / 100, 2))
                        . ' (' . Yii::$app->formatter->asPercent100($minimum_percentage_to_settle) . ' estimate - waiting for placement)';
                    $this->_activeCustomerCreditorsWithSettlementCushion[] = [
                        'name' => $customerCreditor->initial_name,
                        'balanceDueOnDebt' => $balanceDueOnDebt
                    ];
                    $balanceDueOnDebt = Yii::$app->formatter->asCurrency(round($customerCreditor->original_balance * $customerCreditor->settlement_cushion_percent / 100, 2))
                        . ' (' . Yii::$app->formatter->asPercent100($customerCreditor->settlement_cushion_percent) . ' Settlement Cushion)*';
                    $this->_activeCustomerCreditorsWithSettlementCushion[] = [
                        'name' => $customerCreditor->initial_name,
                        'balanceDueOnDebt' => $balanceDueOnDebt
                    ];
                } else {
                    $this->_activeCustomerCreditorsWithSettlementCushion[] = [
                        'name' => $customerCreditor->initial_name,
                        'balanceDueOnDebt' => $customerCreditor->getBalanceDueOnDebt()
                    ];
                }
            }
            if ($fSettlementCushion) {
                $this->_activeCustomerCreditorsWithSettlementCushion[] = [];
                $this->_activeCustomerCreditorsWithSettlementCushion[] = [
                    'name' => '',
                    'balanceDueOnDebt' => '* Extra money will be applied toward principal'
                ];
            }
        }

        return $this->_activeCustomerCreditorsWithSettlementCushion;
    }

    /**
     * @return \yii\db\ActiveQuery|CustomerCreditorQuery
     */
    public function getNotActiveCustomerCreditors()
    {
        /** @var CustomerCreditorQuery $query */
        $query = $this->hasMany(CustomerCreditor::class, ['customer_id' => 'id']);

        return $query->notIncluded();
    }

    /**
     * @return \yii\db\ActiveQuery|CustomerCreditorQuery
     */
    public function getActiveCustomerCreditors()
    {
        /** @var CustomerCreditorQuery $query */
        $query = $this->hasMany(CustomerCreditor::class, ['customer_id' => 'id']);

        return $query->included();
    }

    /**
     * @return \yii\db\ActiveQuery|CustomerCreditorQuery
     */
    public function getNotClosedActiveCustomerCreditors()
    {
        /** @var CustomerCreditorQuery $query */
        $query = $this->hasMany(CustomerCreditor::class, ['customer_id' => 'id']);

        return $query->notClosed()->indexBy('id');
    }

    /**
     * @return \yii\db\ActiveQuery|CustomerCreditorQuery
     */
    public function getCompletedCustomerCreditors()
    {
        /** @var CustomerCreditorQuery $query */
        $query = $this->hasMany(CustomerCreditor::class, ['customer_id' => 'id']);

        return $query->with('creditorOfferAccepted')->completed();
    }

    /**
     * @return \yii\db\ActiveQuery|CustomerCreditorQuery
     */
    public function getSummonsActiveCustomerCreditors()
    {
        return $this->hasMany(CustomerCreditor::className(), ['customer_id' => 'id'])
            ->andWhere(['customer_creditor.include' => CustomerCreditor::INCLUDE_YES])
            ->andWhere(['=', 'customer_creditor.status', CustomerCreditor::STATUS_SUMMONS_RECEIVED])
            ->indexBy('id')
            ;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDatas()
    {
        return $this->hasMany(Data::className(), ['customer_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocuments()
    {
        return $this->hasMany(Document::className(), ['customer_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReviewDocuments()
    {
        return $this->hasMany(Document::className(), ['customer_id' => 'id'])->onCondition(['document.is_reviewed' => false, 'document.is_deleted' => false]);
    }

    /**
     * @return \yii\db\ActiveQuery|DraftQuery
     */
    public function getDrafts()
    {
        return $this->hasMany(Draft::className(), ['customer_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEmailCustomers()
    {
        return $this->hasMany(EmailCustomer::className(), ['customer_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery|CustomerReminderQuery
     */
    public function getReminders()
    {
        return $this->hasMany(CustomerReminder::className(), ['customer_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNotes()
    {
        return $this->hasMany(Note::className(), ['customer_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHotNotes()
    {
        return $this->hasMany(HotNote::className(), ['customer_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRam()
    {
        return $this->hasOne(Ram::className(), ['customer_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGcs()
    {
        return $this->hasOne(Gcs::className(), ['customer_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlanOriginal()
    {
        return $this->hasOne(PlanHistory::class, ['customer_id' => 'id'])
            ->orderBy('ins_ts')
            ->limit(1);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlanCurrent()
    {
        return $this->hasOne(PlanHistory::class, ['customer_id' => 'id'])->andOnCondition(['plan_history.type' => PlanHistory::TYPE_CURRENT]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlanHistory()
    {
        return $this->hasOne(PlanHistory::class, ['id' => 'plan_history_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlanHistories()
    {
        return $this->hasMany(PlanHistory::class, ['customer_id' => 'id'])->orderBy(['ins_ts' => SORT_DESC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLoanPlanCurrent()
    {
        return $this->hasOne(LoanPlanHistory::class, ['customer_id' => 'id'])->andOnCondition(['loan_plan_history.type' => LoanPlanHistory::TYPE_CURRENT]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLoanPlanOriginal()
    {
        return $this->hasOne(LoanPlanHistory::class, ['customer_id' => 'id'])
            ->orderBy('ins_ts ASC')
            ->limit(1);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLoanPlanRefinanced()
    {
        return $this->hasOne(LoanPlanHistory::class, ['customer_id' => 'id'])
            ->andOnCondition(['loan_plan_history.refinanced' => 1])
            ->orderBy('ins_ts DESC')
            ->limit(1);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLoanPlanHistory()
    {
        return $this->hasOne(LoanPlanHistory::class, ['id' => 'loan_plan_history_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLoanPlanHistories()
    {
        return $this->hasMany(LoanPlanHistory::class, ['customer_id' => 'id'])->orderBy(['ins_ts' => SORT_DESC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRefinancedLoanPlanHistories()
    {
        return $this->hasMany(LoanPlanHistory::class, ['customer_id' => 'id'])
            ->andOnCondition(['loan_plan_history.refinanced' => 1])
            ->orderBy(['ins_ts' => SORT_ASC]);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSms()
    {
        return $this->hasMany(Sms::className(), ['customer_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTasks()
    {
        return $this->hasMany(Task::className(), ['customer_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery|CustomerTokenQuery
     */
    public function getTokens()
    {
        return $this->hasMany(CustomerToken::className(), ['customer_id' => 'id'])->active();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getData()
    {
        return $this->hasOne(Data::className(), ['customer_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery|CustomerCreditorQuery
     */
    public function getHistory()
    {
        return $this->hasMany(History::className(), ['customer_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery|CustomerCreditorQuery
     */
    public function getHistoryLastChangeStatus()
    {
        return $this->hasOne(History::className(), ['customer_id' => 'id'])
            ->onCondition(['history.event' => History::EVENT_CUSTOMER_CHANGE_STATUS])
            ->orderBy(['history.ins_ts' => SORT_DESC]);
    }

    /**
     * @return \yii\db\ActiveQuery|CustomerCreditorQuery
     */
    public function getCustomerCreditorsAggregation()
    {
        //ss();
        return $this->getCustomerCreditors()
            ->select(['customer_creditor.customer_id', 'counted' => 'count(*)'])
            ->included()
            ->groupBy('customer_creditor.customer_id')
            ->asArray(true);
    }

    /**
     * @return CustomerCreditorQuery
     */
    public function getCustomerCreditorsSettledAggregation()
    {
        return $this
            ->getCustomerCreditorsAggregation()
            ->settled();
    }

    /**
     * @return CustomerCreditorQuery
     */
    public function getCustomerCreditorsNotSettledAggregation()
    {
        return $this
            ->getCustomerCreditorsAggregation()
            ->notClosed()
            ->notCombinedZeroBalance()
            ->notLoanPlacementWait();
    }

    /**
     * @return \yii\db\ActiveQuery|CustomerCreditorQuery
     */
    public function getNotAcceptedOffersAggregation()
    {
        return $this->getCustomerCreditors()
            ->select([
                'customer_creditor.id',
                'countAcceptedOffers' => CreditorOffer::find()
                    ->andWhere('creditor_offer.customer_creditor_id = customer_creditor.id')
                    ->accepted()
                    ->select('count(*)')
            ])
            ->included()
            ->settled()
            ->groupBy('customer_creditor.id')
            ->asArray(true);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLastAgreement()
    {
        return $this->hasOne(Document::className(), ['customer_id' => 'id'])->andWhere([
            'type' => Document::TYPE_ENROLLMENT
        ])->orderBy('ins_ts DESC')->limit(1);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPreviousAgreement()
    {
        return $this->hasOne(Document::className(), ['customer_id' => 'id'])->andWhere([
                'type' => Document::TYPE_ENROLLMENT
            ])
            ->orderBy('ins_ts DESC')
            ->offset(1)
            ->limit(1);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTrickleSystemActive()
    {
        return $this
            ->hasOne(TrickleSystem::class, ['customer_id' => 'id'])
            ->onCondition(['trickle_system.status' => TrickleSystem::STATUS_ACTIVE]);
    }

    /**
     * @return \yii\db\ActiveQuery|CustomerStarredQuery
     */
    public function getCustomerStarred()
    {
        return $this->hasMany(CustomerStarred::class, ['customer_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery|CustomerStarredQuery
     */
    public function getCustomerStarredCurrentUser()
    {
        //TODO не использовать тут Yii::$app->user перенести в бекенд когда выложим ветку
        return $this
            ->hasOne(CustomerStarred::class, ['customer_id' => 'id'])
            ->onCondition(['customer_starred.user_id' => Yii::$app->user->id]);
    }

    /**
     * @return array
     */
    public static function getApplicantTypeTexts()
    {
        return [
            self::APPLICANT_TYPE_INDIVIDUAL => Yii::t('app', 'Individual '),
            self::APPLICANT_TYPE_JOINT => Yii::t('app', 'Joint')
        ];
    }

    /**
     * @param null|int $currentReasonId Used to search for deleted hardship reasons (for compatibility)
     * @return array
     */
    public static function getHardshipReasonTexts($currentReasonId = null)
    {
        $reasons = [
            self::HARDSHIP_REASON_AVOID_BANKRUPTCY => Yii::t('app', 'Avoid Bankruptcy'),
            self::HARDSHIP_REASON_DIVORCED => Yii::t('app', 'Divorced'),
            self::HARDSHIP_REASON_ILLNESS_IN_FAMILY => Yii::t('app', 'Illness in Family'),
            self::HARDSHIP_REASON_LOSS_OF_INCOME => Yii::t('app', 'Loss of Income'),
            self::HARDSHIP_REASON_WIDOWED => Yii::t('app', 'Widowed'),
            self::HARDSHIP_REASON_MEDICAL_ISSUES => Yii::t('app', 'Medical Issues'),
            self::HARDSHIP_REASON_OTHER => Yii::t('app', 'Other'),
            self::HARDSHIP_REASON_BIRTH => Yii::t('app', 'Birth'),
            self::HARDSHIP_REASON_SPECIAL_NEEDS_FAMILY => Yii::t('app', 'Special Needs Family'),
            self::HARDSHIP_REASON_LOSS_OF_JOB => Yii::t('app', 'Loss of Job'),
            self::HARDSHIP_REASON_LAID_OFF => Yii::t('app', 'Laid Off'),
            self::HARDSHIP_REASON_UNEXPECTED_EXPENSES => Yii::t('app', 'Unexpected Expenses'),
        ];

        if ($currentReasonId && isset(self::getDelHardshipReasonTexts()[$currentReasonId])) {
            $reasons[$currentReasonId] = self::getDelHardshipReasonTexts()[$currentReasonId];
        }

        //Sort hardship reasons alphabetically
        natsort($reasons);

        return $reasons;
    }

    /**
     * @return string
     */
    public function getHardshipReasonText()
    {
        return self::getHardshipReasonTexts()[$this->hardship_reason] ?? $this->hardship_reason;
    }

    /**
     * List of deleted hardship reasons
     * @return array
     */
    public static function getDelHardshipReasonTexts()
    {
        $reasons = [
            self::HARDSHIP_REASON_DEBT_TO_INCOME_TO_HIGH => Yii::t('app', 'Debt To Income To High (Del.)'),
            self::HARDSHIP_REASON_HIGH_INTEREST_RATES => Yii::t('app', 'High Interest Rates (Del.)'),
            self::HARDSHIP_REASON_NOT_ABLE_TO_SAVE => Yii::t('app', 'Not able to Save (Del.)'),
        ];

        return $reasons;
    }


    /**
     * @return array
     */
    public static function getFileStatusTexts()
    {
        return [
            self::FILE_STATUS_NEW => Yii::t('app', 'File Status New'),
            self::FILE_STATUS_COMPLIANCE_CALL_NEEDED => Yii::t('app', 'Compliance Call Needed'),
            self::FILE_STATUS_COMPLIANCE_CALL_ATTEMPT => Yii::t('app', 'Compliance Call Attempt'),
            self::FILE_STATUS_COMPLIANCE_CALL_NOT_COMPLETED => Yii::t('app', 'Compliance Call NOT Completed'),
            self::FILE_STATUS_COMPLIANCE_CALL_COMPLETED => Yii::t('app', 'Compliance Call Completed'),
            self::FILE_STATUS_1_WC_ATTEMPT => Yii::t('app', '1st WC attempt'),
            self::FILE_STATUS_2_WC_ATTEMPT => Yii::t('app', '2nd WC attempt'),
            self::FILE_STATUS_3_WC_ATTEMPT => Yii::t('app', '3rd WC attempt'),
            self::FILE_STATUS_4_WC_ATTEMPT => Yii::t('app', '4th WC attempt'),
            self::FILE_STATUS_5_WC_ATTEMPT => Yii::t('app', '5th WC attempt'),
            self::FILE_STATUS_6_WC_ATTEMPT => Yii::t('app', '6th WC attempt'),
            self::FILE_STATUS_WELCOME_PACK_CALL_COMPLETED => Yii::t('app', 'Welcome Pack Call Completed'),

            self::FILE_STATUS_30_DAY_ATTEMPT_1 => Yii::t('app', '30 Day attempt - 1'),
            self::FILE_STATUS_30_DAY_ATTEMPT_2 => Yii::t('app', '30 Day Call attempt - 2'),
            self::FILE_STATUS_30_DAY_ATTEMPT_3 => Yii::t('app', '30 Day Call attempt - 3'),
            self::FILE_STATUS_30_DAY_COMPLETED => Yii::t('app', '30 Day Call Completed'),

            self::FILE_STATUS_60_DAY_CALL_ATTEMPTED => Yii::t('app', '60 Day Call Attempted'),
            self::FILE_STATUS_60_DAY_CALL_ATTEMPTED_2 => Yii::t('app', '60 Day Call Attempted - 2'),
            self::FILE_STATUS_60_DAY_CALL_ATTEMPTED_3 => Yii::t('app', '60 Day Call Attempted - 3'),
            self::FILE_STATUS_60_DAY_CALL_ATTEMPTED_4 => Yii::t('app', '60 Day Call Attempted - 4'),
            self::FILE_STATUS_60_DAY_CALL_ATTEMPTED_5 => Yii::t('app', '60 Day Call Attempted - 5'),
            self::FILE_STATUS_60_DAY_STRATEGY_CALL_COMPLETED => Yii::t('app', '60 Day Strategy Call Completed'),

            self::FILE_STATUS_90_DAY_CALL_ATTEMPTED => Yii::t('app', '90 Day Call Attempted'),
            self::FILE_STATUS_90_DAY_STRATEGY_CALL_COMPLETED => Yii::t('app', '90 Day Strategy Call Completed'),
            self::FILE_STATUS_CANCELLED_BEFORE_WCC => Yii::t('app', 'Cancelled before WCC'),
            self::FILE_STATUS_CANCELLED_DURING_WC => Yii::t('app', 'Cancelled during WC'),
            self::FILE_STATUS_COMPLETED_PROGRAM => Yii::t('app', 'Completed Program'),
            self::FILE_STATUS_PROGRAM_REVIEW_CALL_COMPLETED => Yii::t('app', 'Program Review Call Completed'),
            self::FILE_STATUS_WELCOME_PACK_CALL_ATTEMPTED => Yii::t('app', 'Welcome Pack Call attempted'),

            self::FILE_STATUS_WC_AUTO_SMS_EMAIL => Yii::t('app', 'WC Auto: SMS/Email'),
            self::FILE_STATUS_WC_AUTO_SCHEDULED => Yii::t('app', 'WC Auto: Scheduled'),
            self::FILE_STATUS_WC_AUTO_SUCCESS => Yii::t('app', 'WC Auto: Success'),
            self::FILE_STATUS_WC_AUTO_NOT_SUCCESS => Yii::t('app', 'WC Auto: Not Success'),
            self::FILE_STATUS_WC_AUTO_CANCELLED => Yii::t('app', 'WC Auto: Cancelled'),

            self::FILE_STATUS_30_DAY_AUTO_SMS_EMAIL => Yii::t('app', '30 day: SMS/Email'),
            self::FILE_STATUS_30_DAY_AUTO_SCHEDULED => Yii::t('app', '30 day: Scheduled'),
            self::FILE_STATUS_30_DAY_AUTO_SUCCESS => Yii::t('app', '30 day: Success'),
            self::FILE_STATUS_30_DAY_AUTO_NOT_SUCCESS => Yii::t('app', '30 day: Not Success'),
            self::FILE_STATUS_30_DAY_AUTO_CANCELLED => Yii::t('app', '30 day: Cancelled'),
       ];
    }

    /**
     * @return array
     */
    public static function getCollectionRecoveryFileStatusTexts()
    {
        return [
                self::FILE_STATUS_RECOVERY_REVIEW => Yii::t('app', 'Recovery review'),
                self::FILE_STATUS_COLLECTABLE => Yii::t('app', 'Collectable'),
                self::FILE_STATUS_COLLECTION_LETTER_SENT => Yii::t('app', 'Collection letter sent'),
                self::FILE_STATUS_COLLECTION_CALL_1 => Yii::t('app', 'Collection Call 1'),
                self::FILE_STATUS_COLLECTION_LETTER_2 => Yii::t('app', 'Collection Letter 2'),
                self::FILE_STATUS_COLLECTION_CALL_2 => Yii::t('app', 'Collection Call 2'),
                self::FILE_STATUS_COLLECTION_LETTER_3 => Yii::t('app', 'Collection Letter 3'),
                self::FILE_STATUS_COLLECTION_CALL_3 => Yii::t('app', 'Collection Call 3'),
                self::FILE_STATUS_COLLECTION_LETTER_4_FINAL => Yii::t('app', 'Collection Letter 4 Final'),
                self::FILE_STATUS_COLLECTION_CALL_4 => Yii::t('app', 'Collection Call 4'),
                self::FILE_STATUS_WRITE_OFF => Yii::t('app', 'Write Off'),
                self::FILE_STATUS_SEND_TO_SMALL_CLAIMS => Yii::t('app', 'Send To Small Claims'),
                self::FILE_STATUS_INSTALLMENT_PLAN_SET => Yii::t('app', 'Installment Plan Set'),
                self::FILE_STATUS_PAID_IN_FULL => Yii::t('app', 'Paid In Full'),
        ];
    }

    /**
     * @return array
     */
    public static function getCollectionStatusTexts()
    {
        return [
            self::COLLECTION_STATUS_PROMISE_TO_PAY => Yii::t('app', 'Promise to Pay'),
            self::COLLECTION_STATUS_OUT_TO_RAISE => Yii::t('app', 'Out to Raise'),
            self::COLLECTION_STATUS_PARTIAL_PAYMENT_ARRANGEMENT => Yii::t('app', 'Partial Payment Arrangement'),
            self::COLLECTION_STATUS_BROKEN_PROMISE => Yii::t('app', 'Broken Promise'),
            self::COLLECTION_STATUS_REFUSE_TO_PAY => Yii::t('app', 'Refuse to Pay'),
            self::COLLECTION_STATUS_BANKRUPTCY => Yii::t('app', 'Bankruptcy'),
            self::COLLECTION_STATUS_DECEASE => Yii::t('app', 'Decease'),
            self::COLLECTION_STATUS_RETAINED_AN_ATTORNEY => Yii::t('app', 'Retained an Attorney'),
            self::COLLECTION_STATUS_SKIP_ACCOUNT => Yii::t('app', 'Skip account'),
            self::COLLECTION_STATUS_PAID_IN_FULL => Yii::t('app', 'Paid in Full'),
            self::COLLECTION_STATUS_BALANCE_IN_FULL => Yii::t('app', 'Balance in Full'),
            self::COLLECTION_STATUS_SETTLED_IN_FULL=> Yii::t('app', 'Settled in Full')
        ];
    }

    /**
     * @param $status
     * @return mixed|null
     */
    public static function getCollectionStatusTextByStatus($status)
    {
        return static::getCollectionStatusTexts()[$status] ?? $status;
    }

    /**
     * @return string
     */
    public function getCollectionStatusText()
    {
        return self::getCollectionStatusTextByStatus($this->collection_recovery_status);
    }

    /**
     * @return string
     */
    public function getFileStatusText()
    {
        $a = self::getFileStatusTexts() + self::getCollectionRecoveryFileStatusTexts();
        $status = $this->status == self::STATUS_COLLECTION_RECOVERY ? $this->collection_recovery_file_status : $this->file_status;
        return $a[$status] ?? $this->file_status;
    }

    /**
     * @param $status
     * @return mixed|null
     */
    public static function getFileStatusTextByStatus($status)
    {
        $a = static::getFileStatusTexts() + static::getCollectionRecoveryFileStatusTexts();
        return $a[$status] ?? null;
    }

    /**
     * @return array
     */
    public static function getPaymentStatusTexts()
    {
        return [
            self::PAYMENT_STATUS_NEW => Yii::t('app', 'Payment Status New'),
            self::PAYMENT_STATUS_UNABLE_TO_RETAIN => Yii::t('app', 'Unable To Retain'),
            self::PAYMENT_STATUS_RETAINED => Yii::t('app', 'Retained'),
            self::PAYMENT_STATUS_NSF_ATTEMPT_1 => Yii::t('app', 'NSF Attempt 1'),
            self::PAYMENT_STATUS_NSF_ATTEMPT_2 => Yii::t('app', 'NSF Attempt 2'),
            self::PAYMENT_STATUS_NSF_ATTEMPT_3 => Yii::t('app', 'NSF Attempt 3'),
            self::PAYMENT_STATUS_NSF_ATTEMPT_4 => Yii::t('app', 'NSF Attempt 4'),
            self::PAYMENT_STATUS_NSF_ATTEMPT_5 => Yii::t('app', 'NSF Attempt 5'),
            self::PAYMENT_STATUS_48_HOURS_TO_CX => Yii::t('app', '48 Hours To CX'),
            self::PAYMENT_STATUS_24_HOURS_TO_CX => Yii::t('app', '24 Hours To CX'),
            self::PAYMENT_STATUS_48_HOURS_TO_REACTIVATION => Yii::t('app', '48 hours to Reactivation'),
            self::PAYMENT_STATUS_24_HOURS_TO_REACTIVATION => Yii::t('app', '24 hours to Reactivation'),
        ];
    }

    /**
     * @return string
     */
    public function getPaymentStatusText()
    {
        return self::getPaymentStatusTexts()[$this->payment_status] ?? $this->payment_status;
    }

    /**
     * @param $status
     * @return mixed|null
     */
    public static function getPaymentStatusTextByStatus($status)
    {
        return static::getPaymentStatusTexts()[$status] ?? $status;
    }

    /**
     * @return array
     */
    public static function getStatusTexts()
    {
        return LeadCustomer::getStatusTexts() + DealCustomer::getStatusTexts() + LoanCustomer::getStatusTexts();
    }

    /**
     * @param $status
     * @return mixed|null
     */
    public static function getStatusTextByStatus($status)
    {
        return static::getStatusTexts()[$status] ?? $status;
    }

    /**
     * @return string
     */
    public function getStatusText()
    {
        return static::getStatusTextByStatus($this->status);
    }

    /**
     * @return array
     */
    public static function getTypeTexts()
    {
        return [
            self::TYPE_LEAD => Yii::t('app', 'Lead'),
            self::TYPE_DEAL => Yii::t('app', 'Deal'),
            self::TYPE_LOAN => Yii::t('app', 'Loan'),
        ];
    }

    /**
     * @param $type
     * @return mixed
     */
    public static function getTypeTextByType($type)
    {
        return self::getTypeTexts()[$type] ?? $type;
    }

    /**
     * @return string
     */
    public function getTypeText()
    {
        return self::getTypeTextByType($this->type);
    }

    /**
     * @return string
     */
    public function getIsDeal()
    {
        return $this->type == self::TYPE_DEAL;
    }

    /**
     * @return string
     */
    public function getIsLoan()
    {
        return $this->type == self::TYPE_LOAN;
    }

    /**
     * @return string
     */
    public function getIsLead()
    {
        return $this->type == self::TYPE_LEAD;
    }

    /**
     * @return array
     */
    public static function getQualityTexts()
    {
        return [
            self::QUALITY_ACTIVE => Yii::t('app', 'Active'),
            self::QUALITY_REJECTED => Yii::t('app', 'Rejected'),
            self::QUALITY_COMMUNITY => Yii::t('app', 'Community'),
            self::QUALITY_UNASSIGNED => Yii::t('app', 'Unassigned'),
            self::QUALITY_TRICKLE => Yii::t('app', 'Trickle'),
        ];
    }

    /**
     * @param $quality
     * @return mixed|null
     */
    public static function getQualityTextByQuality($quality)
    {
        return self::getQualityTexts()[$quality] ?? $quality;
    }

    /**
     * @return mixed|string
     */
    public function getQualityText()
    {
        return self::getQualityTextByQuality($this->quality);
    }

    /**
     * @return array
     */
    public static function getSourceTexts()
    {
        $models = self::find()
            ->select(['source'])
            ->groupBy('source')
            ->andWhere('source IS NOT NULL')
            ->andWhere(['!=', 'source', ''])
            ->asArray()
            ->all();

        return ArrayHelper::map($models, 'source', 'source');
    }

    /**
     * @return array
     */
    public static function getSourceManualTexts()
    {
        return [
            self::SOURCE_CREDIT_ORG => self::SOURCE_CREDIT_ORG,
            self::SOURCE_CREDIT_SESAME => self::SOURCE_CREDIT_SESAME,
            self::SOURCE_GOOGLE => self::SOURCE_GOOGLE,
            self::SOURCE_FACEBOOK => self::SOURCE_FACEBOOK,
            self::SOURCE_OTHER_DIGITAL => self::SOURCE_OTHER_DIGITAL,
            self::SOURCE_REFERRAL => self::SOURCE_REFERRAL,
            self::SOURCE_MAILER_NO_CODE => self::SOURCE_MAILER_NO_CODE,
            self::SOURCE_OTHER => self::SOURCE_OTHER,
        ];
    }

    /**
     * @return int|mixed
     */
    public function getSourceText()
    {
        return $this->source;
    }

    /**
     * @return string
     */
    public function getSourceWithCampaign()
    {
        return $this->source . ($this->campaign ? " $this->campaign" : '');
    }

    /**
     * @return string
     */
    public function getLoanCompanyByState()
    {
        return Yii::t('app', 'Credit9');
        //return in_array($this->primaryApplicant->state, Yii::$app->params['AmericorLoanStates']) ? Yii::t('app', 'Americor') : Yii::t('app', 'Credit9');
    }


    /**
     * @return array
     */
    public static function getSchedulePaymentsTypeTexts()
    {
        return [
            self::SCHEDULE_PAYMENTS_TYPE_MONTHLY => Yii::t('app', 'Monthly'),
            self::SCHEDULE_PAYMENTS_TYPE_BI_MONTHLY => Yii::t('app', 'Bi-Monthly'),
            self::SCHEDULE_PAYMENTS_TYPE_BI_WEEKLY => Yii::t('app', 'Bi-Weekly'),
        ];
    }

    /**
     * @return mixed|string
     */
    public function getSchedulePaymentsTypeText()
    {
        return self::getSchedulePaymentsTypeTexts()[$this->schedule_payments_type] ?? $this->schedule_payments_type;
    }

    /**
     * @return array
     */
    public static function getLoanIsSignedTexts()
    {
        return [
            self::LOAN_IS_SIGNED_NO => Yii::t('app', 'No'),
            self::LOAN_IS_SIGNED_YES => Yii::t('app', 'Yes'),
            self::LOAN_IS_SIGNED_SENT => Yii::t('app', 'Sent'),
        ];
    }

    /**
     * @return string
     */
    public function getLoanIsSignedText()
    {
        return self::getLoanIsSignedTexts()[$this->loan_is_signed] ?? $this->loan_is_signed;
    }

    /**
     * @return int
     */
    public function getLoanPaymentIntervalText()
    {
        return PlanHistory::getScheduleTypeTexts()[$this->loan_payment_interval] ?? $this->loan_payment_interval;
    }

    /**
     * @return array
     */
    public static function getAutomationSrAiLastActionTexts()
    {
        return [
            self::AUTOMATION_SR_AI_LAST_ACTION__SMS_SENT => Yii::t('app', 'Sent sms'),
            self::AUTOMATION_SR_AI_LAST_ACTION__CALLED_CUSTOMER_AND_LEFT_VOICEMAIL => Yii::t('app', 'Called customer and left a voicemail'),
            self::AUTOMATION_SR_AI_LAST_ACTION__SUCCESS_RECEIVED_AN_EMAIL => Yii::t('app', 'Email received'),
            self::AUTOMATION_SR_AI_LAST_ACTION__CALLED_CUSTOMER_NO_ANSWER => Yii::t('app', 'Called a customer - no answer'),
            self::AUTOMATION_SR_AI_LAST_ACTION__SUCCESS_CALL => Yii::t('app', 'Successful call'),
            self::AUTOMATION_SR_AI_LAST_ACTION__SUCCESS_INBOUND_CALL => Yii::t('app', 'Successful inbound call'),
            self::AUTOMATION_SR_AI_LAST_ACTION__INBOUND_CALL_FAIL => Yii::t('app', 'Inbound call failed'),
            self::AUTOMATION_SR_AI_LAST_ACTION__SUCCESS_RECEIVED_AN_SMS => Yii::t('app', 'SMS received'),
            self::AUTOMATION_SR_AI_LAST_ACTION__EMAIL_SENT => Yii::t('app', 'Email sent'),
            self::AUTOMATION_SR_AI_LAST_ACTION__SEQUENCE_COMPLETED => Yii::t('app', 'Sequence completed'),
        ];
    }

    /**
     * @return mixed|string
     */
    public function getAutomationSrAiLastActionText()
    {
        return self::getAutomationSrAiLastActionTexts()[$this->automation_sr_ai_last_action] ?? $this->automation_sr_ai_last_action;
    }

    /**
     * @param array $keys
     * @return array
     */
    public static function getInformationLabelTexts($keys = [])
    {
        $a = [
            self::INFORMATION_LABEL_MISSED_CALL => Yii::t('app', 'Missed Call'),
            self::INFORMATION_LABEL_NEW_SMS => Yii::t('app', 'New SMS'),
            self::INFORMATION_LABEL_2ND_DAY_CALL => Yii::t('app', 'Next Day Call'),
            self::INFORMATION_LABEL_AUTO_REJECT_NO_SCHEDULE_CALL => Yii::t('app', 'Auto Reject. No schedule call')
        ];

        return $keys ? array_filter($a, function ($key) use ($keys) {
            return in_array($key, $keys);
        }, ARRAY_FILTER_USE_KEY) : $a;
    }

    /**
     * @return array
     */
    public static function getInformationLabelAttributes()
    {
        return [
            self::INFORMATION_LABEL_MISSED_CALL => 'has_missed_call',
            self::INFORMATION_LABEL_NEW_SMS => 'has_new_sms',
            self::INFORMATION_LABEL_2ND_DAY_CALL => 'has_2nd_day_call'
        ];
    }

    /**
     * @param $value
     * @return string
     */
    public static function getInformationLabelAttribute($value)
    {
        return self::getInformationLabelAttributes()[$value] ?? false;
    }

    /**
     * @return array
     */
    public function getInformationLabels()
    {
        $a = self::getInformationLabelTexts();
        $result = [];
        foreach (self::getInformationLabelAttributes() as $key => $value) {
            if ($this->{$value}) {
                $result[$key] = $a[$key] ?? $key;
            }
        }
        return $result;
    }

    /**
     * @return null|string
     */
    public function getName()
    {
        return $this->primary_applicant_id ? $this->primaryApplicant->name : null;
    }

    /**
     * @return null|string
     */
    public function getFirstName()
    {
        return $this->primary_applicant_id ? $this->primaryApplicant->first_name : null;
    }

    /**
     * @return null|string
     */
    public function getLastName()
    {
        return $this->primary_applicant_id ? $this->primaryApplicant->last_name : null;
    }

    /**
     * @return null|string
     */
    public function getCoName()
    {
        return $this->co_applicant_enabled && $this->co_applicant_id ? $this->coApplicant->name : null;
    }

    /**
     * @return string
     */
    public function getName2()
    {
        if ($this->name && $this->coName) {
            return "p: {$this->name} / co: {$this->coName}";
        } else {
            return $this->name;
        }
    }


    /**
     * @param string $delimiter
     * @return string
     */
    public function getNames($delimiter = '/')
    {
        $names = [];
        if ($this->name) {
            $names[] = $this->name;
        }
        if ($this->coName) {
            $names[] = $this->coName;
        }

        return implode($delimiter, $names);
    }

    /**
     * @return null|string
     */
    public function getEmail()
    {
        return $this->primary_applicant_id ? $this->primaryApplicant->email : null;
    }

    /**
     * @return null|string
     */
    public function getCoEmail()
    {
        return $this->co_applicant_enabled && $this->co_applicant_id ? $this->coApplicant->email : null;
    }

    /**
     * @param string $delimiter
     * @return string
     */
    public function getEmails($delimiter = '/')
    {
        $emails = [];
        if ($this->email) {
            $emails[] = $this->email;
        }
        if ($this->coEmail) {
            $emails[] = $this->coEmail;
        }

        return implode($delimiter, $emails);
    }


    /**
     * @return null|string
     */
    public function getAge()
    {
        if ($this->primary_applicant_id) {
            $datetime = new \DateTime($this->primaryApplicant->dob);
            $now = new \DateTime();
            $interval = $datetime->diff($now);
            return $interval->format('%y');
        }
        return null;
    }

    /**
     * @return array
     */
    public function getPhonesList()
    {
        $result = [];

        if ($this->primary_applicant_id && $this->primaryApplicant->phone_mobile) {
            $result[$this->primary_applicant_id] = $this->name . ' (' . $this->primaryApplicant->phone_mobile . ')';
        }

        if ($this->co_applicant_enabled && $this->co_applicant_id && $this->coApplicant->phone_mobile) {
            $result[$this->co_applicant_id] = $this->coApplicant->name . ' (' . $this->coApplicant->phone_mobile . ')';
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getPhonesAlert()
    {
        //please enter mobile phone number on profile page
        $result = [];

        if ($this->primary_applicant_id && !$this->primaryApplicant->phone_mobile) {
            $result[$this->primary_applicant_id] = 'Primary Applicant: please enter mobile phone number on profile page.';
        }

        if ($this->co_applicant_enabled && $this->co_applicant_id && !$this->coApplicant->phone_mobile) {
            $result[$this->co_applicant_id] = 'CoApplicant: please enter mobile phone number on profile page.';
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getPrimaryApplicantPhone()
    {
        return $this->primary_applicant_id ? $this->primaryApplicant->phone : null;
    }

    /**
     * @return array
     */
    public function getApplicantsEmailList()
    {
        $result = [];
        if ($this->primary_applicant_id && $this->primaryApplicant->email) {
            $result[$this->primary_applicant_id] = $this->name . ' (' . $this->primaryApplicant->email . ')';
        }
        if ($this->co_applicant_id && $this->coApplicant->email) {
            $result[$this->co_applicant_id] = $this->coApplicant->name . ' (' . $this->coApplicant->email . ')';
        }
        return $result;
    }

    /**
     * @param array $params
     * @param string $tab
     * @param bool $scheme
     * @return string
     */
    public function getUrl($params = [], $tab = 'main', $scheme = false)
    {
        if (isset(self::$_routes[$this->type])) {
            return Url::toRoute(array_merge(['/' . self::$_routes[$this->type] . '/' . $tab, 'id' => $this->id], $params), $scheme);
        }
        return '';
    }

    /**
     * @return int
     */
    public function getCount_emails()
    {
        return $this->getEmailCustomers()->count();
    }

    /**
     * @return int
     */
    public function getCount_sms()
    {
        return $this->getSms()->count();
    }

    /**
     * @return int
     */
    public function getCount_calls()
    {
        return $this->getCalls()->count();
    }

    /**
     * @return int
     */
    public function getCount_creditors()
    {
        return empty($this->customerCreditorsAggregation) ? 0 : $this->customerCreditorsAggregation[0]['counted'];
    }

    /**
     * @return int
     */
    public function getCount_settled_creditors()
    {
        return empty($this->customerCreditorsSettledAggregation) ? 0 : $this->customerCreditorsSettledAggregation[0]['counted'];
    }

    /**
     * @return int
     */
    public function getCount_not_settled_creditors()
    {
        return empty($this->customerCreditorsNotSettledAggregation) ? 0 : $this->customerCreditorsNotSettledAggregation[0]['counted'];
    }

    /**
     * @return int
     */
    public function getCount_not_completed_creditors()
    {
        return empty($this->customerCreditorsNotCompletedAggregation) ? 0 : $this->customerCreditorsNotCompletedAggregation[0]['counted'];
    }

    /**
     * @return CustomerCreditorsTotal
     */
    public function getCustomerCreditorsTotal()
    {
        if (!isset($this->_customerCreditorsTotal)) {
            $this->_customerCreditorsTotal = new CustomerCreditorsTotal([
                'owner' => $this,
            ]);
        }
        return $this->_customerCreditorsTotal;
    }

    /**
     * @return DraftsTotal
     */
    public function getDraftsTotal()
    {
        if (!isset($this->_draftsTotal)) {
            $this->_draftsTotal = new DraftsTotal([
                'owner' => $this,
            ]);
        }

        return $this->_draftsTotal;
    }

    /**
     * @return BankFeeDraftsTotal
     */
    public function getBankFeeDraftsTotal()
    {
        if (!isset($this->_bankFeeDraftsTotal)) {
            $this->_bankFeeDraftsTotal = new BankFeeDraftsTotal([
                'owner' => $this,
            ]);
        }

        return $this->_bankFeeDraftsTotal;
    }

    /**
     * @param null|integer $plan_id
     * @return LoanDraftsTotal
     */
    public function getLoanDraftsTotal($plan_id = null)
    {
        if (is_null($plan_id)) {
            $plan_id = $this->loanPlanCurrent->id ?? null;
        }

        if (!isset($this->_loanDraftsTotals[$plan_id ?? 'current'])) {
            $this->_loanDraftsTotals[$plan_id ?? 'current'] = new LoanDraftsTotal([
                'owner' => $this,
                'plan_id' => $plan_id,
            ]);
        }

        return $this->_loanDraftsTotals[$plan_id ?? 'current'];
    }

    /**
     * @return CreditorPaymentsTotal
     */
    public function getCreditorPaymentsTotal()
    {
        if (!isset($this->_creditorPaymentsTotal)) {
            $this->_creditorPaymentsTotal = new CreditorPaymentsTotal([
                'owner' => $this,
            ]);
        }

        return $this->_creditorPaymentsTotal;
    }


    /**
     * @return BankFeeCreditorPaymentsTotal
     */
    public function getBankFeeCreditorPaymentsTotal()
    {
        if (!isset($this->_bankFeeCreditorPaymentsTotal)) {
            $this->_bankFeeCreditorPaymentsTotal = new BankFeeCreditorPaymentsTotal([
                'owner' => $this,
            ]);
        }

        return $this->_bankFeeCreditorPaymentsTotal;
    }

    /**
     * @return AttorneyPaymentsTotal
     */
    public function getAttorneyPaymentsTotal()
    {
        if (!isset($this->_attorneyPaymentsTotal)) {
            $this->_attorneyPaymentsTotal = new AttorneyPaymentsTotal([
                'owner' => $this,
            ]);
        }

        return $this->_attorneyPaymentsTotal;
    }


    /**
     * @return BankFeeAttorneyPaymentsTotal
     */
    public function getBankFeeAttorneyPaymentsTotal()
    {
        if (!isset($this->_bankFeeAttorneyPaymentsTotal)) {
            $this->_bankFeeAttorneyPaymentsTotal = new BankFeeAttorneyPaymentsTotal([
                'owner' => $this,
            ]);
        }

        return $this->_bankFeeAttorneyPaymentsTotal;
    }

    /**
     * @return SettlementFeesTotal
     */
    public function getSettlementFeesTotal()
    {
        if (!isset($this->_settlementFeesTotal)) {
            $this->_settlementFeesTotal = new SettlementFeesTotal([
                'owner' => $this,
            ]);
        }
        return $this->_settlementFeesTotal;
    }

    /**
     * @return AdvancesAndRecoupsTotal
     */
    public function getAdvancesAndRecoupsTotal()
    {
        if (!isset($this->_advancesAndRecoupsTotal)) {
            $this->_advancesAndRecoupsTotal = new AdvancesAndRecoupsTotal([
                'owner' => $this,
            ]);
        }

        return $this->_advancesAndRecoupsTotal;
    }

//    /**
//     * @return float
//     */
//    public function getTotal_credit_utilization()
//    {
//        return $this->total_credit_limit ? round($this->total_debt / $this->total_credit_limit, 2) : 0;
//    }

    /**
     * @return TotalCalculator
     */
    public function getTotalCalculator()
    {
        if (!isset($this->_totalCalculator)) {
            if ($this->total_debt) {
                if (!(float)$this->total_interest_rate) {
                    $this->total_interest_rate = CreditCardCalculator::DEFAULT_CREDIT_CART_RATE;
                    $this->save(true, ['total_interest_rate']);
                }

                $this->_totalCalculator = new TotalCalculator([
                    'balance' => $this->total_debt,
                    'monthly_payment' => $this->customerCreditorsTotal->monthlyPayments,
                    'rate' => $this->total_interest_rate
                ]);
            } else {
                $this->_totalCalculator = new TotalCalculator();
            }
        }

        return $this->_totalCalculator;
    }

    /**
     * @return TotalCalculator
     */
    public function getTotalCalculatorDefaultPercent()
    {
        if (!isset($this->_totalCalculatorDefaultPercent)) {
            if ($this->total_debt) {
                $this->_totalCalculatorDefaultPercent = new TotalCalculator([
                    'balance' => $this->total_debt,
                    'monthly_payment' => $this->customerCreditorsTotal->monthlyPayments,
                    'rate' => CreditCardCalculator::DEFAULT_CREDIT_CART_RATE
                ]);
            } else {
                $this->_totalCalculatorDefaultPercent = new TotalCalculator();
            }
        }

        return $this->_totalCalculatorDefaultPercent;
    }


    /**
     * @return AmericorCalculator
     */
    public function getAmericorCalculator()
    {
        if (!isset($this->_americorCalculator)) {
            if ($this->total_debt) {
                $this->_americorCalculator = new AmericorCalculator([
                    'creditors' => $this->customerCreditorsTotal->creditorsOnProgram,
                    'balance' => $this->total_debt,
                    'isRamAccount' => $this->isGcsState() ? false : true,
                    'inProgramCustomerCreditors' => $this->activeCustomerCreditors
                ]);
            } else {
                $this->_americorCalculator = new AmericorCalculator();
            }
        }

        return $this->_americorCalculator;
    }

    /**
     * @return LoanCalculator
     */
    public function getLoanCalculator()
    {
        if (!$this->_loanCalculator) {
            $this->_loanCalculator = new LoanCalculator([
                'model' => $this,
            ]);
        }

        return $this->_loanCalculator;
    }

    /**
     * @return LoanCalculator
     */
    public function getFrozenLoanCalculator()
    {
        if (!$this->_frozenLoanCalculator) {
            $this->_frozenLoanCalculator = new FrozenLoanCalculator([
                'model' => $this,
            ]);
        }

        return $this->_frozenLoanCalculator;
    }


    //todo объеденить CurrentLoanCalculator и ActualLoanCalculator
    /**
     * @return LoanCalculator
     */
    public function getCurrentLoanCalculator()
    {
        if (!$this->_currentLoanCalculator) {
            $this->_currentLoanCalculator = new LoanCalculator([
                'model' => $this,
                //'mode' => LoanCalculator::MODE_CURRENT,
            ]);
        }

        return $this->_currentLoanCalculator;
    }

    /**
     * @return LoanCalculator
     */
    public function getActualLoanCalculator()
    {
        if(!$this->_actualLoanCalculator) {
            $this->_actualLoanCalculator = new LoanCalculator([
                'model' => $this,
                //'mode' => $this->type == self::TYPE_LEAD ? LoanCalculator::MODE_ORIGINAL : LoanCalculator::MODE_CURRENT,
            ]);
        }

        return $this->_actualLoanCalculator;
    }

    /**
     * @return LoanCalculator
     */
    public function getPayoffQuoteCalculator()
    {
        if(!$this->_payoffQuoteCalculator) {
            $this->_payoffQuoteCalculator = new PayoffQuoteCalculator([
                'model' => $this,
            ]);
        }

        return $this->_payoffQuoteCalculator;
    }

    //    /**
//     * @return LoanCalculator
//     */
//    public function getFrozenLoanCalculator()
//    {
//        if (!$this->_frozenLoanCalculator) {
//            $this->_frozenLoanCalculator = new LoanCalculator([
//                'model' => $this,
//                'mode' => LoanCalculator::MODE_FROZEN,
//            ]);
//        }
//
//        return $this->_frozenLoanCalculator;
//    }

    /**
     * @return int|string
     */
    public function getTotal_monthly_income()
    {
        return ($this->primaryApplicant ? $this->primaryApplicant->net_monthly_income : 0)
            + ($this->co_applicant_enabled && $this->coApplicant ? $this->coApplicant->net_monthly_income : 0);
    }

    /**
     * @return float|int
     */
    public function getTotal_monthly_expenses()
    {
        $sum = 0;
        foreach ($this->budgetExpenses as $expense) {
            //var_dump($expense); exit;
            if ($expense->formField->type == FormField::TYPE_NUMERIC) {
                $sum += (float)$expense->value;
            }
        }

        return $sum;
    }

    /**
     * @return float
     */
    public function getCalculated_funds_available()
    {
        return $this->total_monthly_income - $this->total_monthly_expenses_including_program_cost;
    }

    /**
     * @return float|int
     */
    public function getMonthly_debt_to_income_ratio()
    {
        return $this->total_monthly_income ? $this->total_monthly_expenses_including_program_cost / $this->total_monthly_income : 0;
    }


    /**
     * @param $phone
     * @return Customer|DealCustomer|LeadCustomer|null
     */
    public static function findByPhone($phone)
    {
        $phone = \Yii::$app->formatter->asE164Phone($phone);

        $applicantIds = Applicant::find()
            ->phone($phone)
            ->select('id')
            ->asArray()
            ->indexBy('id')
            ->all();

        $applicantIds = array_keys($applicantIds);

        return static::find()
            ->applicant($applicantIds)
            ->orderByDuplicate()
            ->limit(1)
            ->one();
    }

    /**
     * @param $email
     * @return Customer|DealCustomer|LeadCustomer|null
     */
    public static function findByEmail($email)
    {
        $applicantIds = Applicant::find()
            ->email($email)
            ->select('id')
            ->asArray()
            ->indexBy('id')
            ->all();

        $applicantIds = array_keys($applicantIds);

        return static::find()
            ->with(['primaryApplicant', 'coApplicant'])
            ->applicant($applicantIds)
            ->orderByDuplicate()
            ->limit(1)
            ->one();
    }

    /**
     * @param $firstName
     * @param $lastName
     * @return Customer|DealCustomer|LeadCustomer|null
     */
    public static function findByFullName($firstName , $lastName)
    {
        $applicantIds = Applicant::find()
            ->fullname($firstName, $lastName)
            ->select('id')
            ->asArray()
            ->indexBy('id')
            ->all();

        $applicantIds = array_keys($applicantIds);

        return static::find()
            ->with(['primaryApplicant', 'coApplicant'])
            ->applicant($applicantIds)
            ->orderByDuplicate()
            ->limit(1)
            ->one();
    }

    /**
     * @param $phone
     * @return int|null
     */
    public function getApplicantIdByPhone($phone)
    {
        if ($this->primaryApplicant && $this->primaryApplicant->existPhone($phone)) {
            return $this->primaryApplicant->id;
        }
        if ($this->coApplicant && $this->coApplicant->existPhone($phone)) {
            return $this->coApplicant->id;
        }
        return null;
    }

    /**
     * @return int
     */
    public function getProgram_month()
    {
        return $this->planCurrent ? $this->planCurrent->months : 0;
    }

    /**
     * @param bool $primary
     * @return string
     */
    public function getPdfCreditReportUrl($primary = true)
    {
        return Url::toRoute([
            self::$_routes[$this->type]. "/get-credit-report",
            'id' => $this->id,
            'primary' => $primary,
            'type' => 'pdf'
        ]);
    }

    /**
     * @return RamComponent
     */
    public function getRamComponent()
    {
        if ($this->ram->mode == Ram::MODE_RAM) {
            return \Yii::$app->ram;
        }

        return null;
    }

    /**
     * @return string
     */
//    public function getPaymentSystemName()
//    {
//        switch ($this->ram->mode) {
//            case Ram::MODE_RAM:
//                return Yii::t('app', 'RAM');
//            case Ram::MODE_GCS:
//                return Yii::t('app', 'GCS');
//            default:
//                return '';
//        }
//    }

    /**
     * @return RamComponent|GcsComponent|null
     */
    public function getAccountManagement()
    {
        switch ($this->ram->mode) {
            case Ram::MODE_RAM:
                return \Yii::$app->ram;
            case Ram::MODE_GCS:
                return \Yii::$app->gcs;
            default:
                return null;
        }
    }

    /**
     * @return bool
     */
    public function isRamAccount()
    {
        return $this->ram && $this->ram->mode == Ram::MODE_RAM;
    }

    /**
     * @return bool
     */
    public function isGcsAccount()
    {
        return $this->ram && $this->ram->mode == Ram::MODE_GCS;
    }

    /**
     * @return bool
     */
    public function isGcsState()
    {
        return isset($this->primaryApplicant) && in_array($this->primaryApplicant->state, Yii::$app->params['gcsStates']);
    }

    /**
     * @return bool
     */
    public function existsGcsAccount()
    {
        return $this->gcs && $this->gcs->isActiveClient();
    }

    /**
     * @param boolean $emptyValue
     * @return array
     */
    public function getActiveCustomerCreditorItems($emptyValue = false)
    {
        $items = [];

        if ($emptyValue) {
            $items[] = [
//                'id' => null,
//                'name' => 'empty',
//                'actual_name' => 'empty',
            ];
        }


        foreach ($this->with('customerCreditors', ['creditorOffers', 'creditor', 'currentCreditor'])->customerCreditors as $customerCreditor) {
            foreach ($customerCreditor->creditorOffers as $creditorOffer) {
                if ($creditorOffer->accepted == CreditorOffer::ACCEPTED_NO) {
                    continue;
                }
                if (
                    $creditorOffer->accepted == CreditorOffer::ACCEPTED_YES
                    || $creditorOffer->accepted == CreditorOffer::ACCEPTED_DELETED
                ) {
                    $items[] = [
                        'id' => $customerCreditor->id,
                        'name' => $customerCreditor->name,
                        'actual_name' => $customerCreditor->actual_name,
                    ];
                    break;
                }
            }
        }
        return $items;
    }

    /**
     * @return false|string
     */
    public function getCurrentDate()
    {
        return date('Y-m-d');
    }

    /**
     * @return string
     */
    public function getDebt_resolution_agreement_date()
    {
        return $this->type == self::TYPE_LEAD ? date('Y-m-d') : $this->enrolled_date;
    }

    /**
     * @return string
     */
    public function getLoanPrimaryApplicantRole()
    {
        return 'Borrower';
    }

    /**
     * @return int
     */
    public function getAmountOfProceedsDistributedDirectlyToYou()
    {
        return 0;
    }

    /**
     * @return string
     */
    public function getLoanCoApplicantRole()
    {
        return $this->co_applicant_enabled && $this->coApplicant ? 'Co-Borrower' : '';
    }

//    /**
//     * @return float
//     */
//    public function getCashInDedicatedRamAccount()
//    {
//        return $this->escrow_balance + 0;
//    }

    /**
     * @return false|string
     */
    public function getUnknownValue()
    {
        return '???????????????????????????????';
    }


    //fixme
    public function getSmsPhoneForRamDocumentQuestions()
    {
        return '+18007194910';
    }

    public function getAccountAgreementPageNumbers($first_page = false)
    {
        $template = DocumentTemplate::find()
            ->type(DocumentTemplate::TYPE_MAIN_CONTRACT)
            ->state($this->primaryApplicant->state)
            ->active()
            ->one();

        if ($this->isGcsState()) {

            $pages = Pdf::getGcsTemplatePageMap($template);

        } else {

            $pages = Pdf::getRamTemplatePageMap($template);
        }

        if ($first_page) {
            return array_keys($pages)[0] ?? null;
        }

        return implode(', ', array_keys($pages));
    }

    /**
     * @param bool $first_page
     * @return null|string
     */
    public function getAtcPageNumbers($first_page = false)
    {
        $template = DocumentTemplate::find()
            ->type(DocumentTemplate::TYPE_MAIN_CONTRACT)
            ->state($this->primaryApplicant->state)
            ->active()
            ->one();

        $pages = Pdf::getAtcTemplatePageMap($template);

        if ($first_page) {
            return array_keys($pages)[0] ?? null;
        }

        return implode(', ', array_keys($pages));
    }


    public function getGcsSponsorName()
    {
        return 'Americor Financial';
    }

    public function getGcsSponsorAccountId()
    {
        return Yii::$app->gcs->company_id;
    }

    public function getAch_applicant_id()
    {
        if ($this->ach_is_non_primary_applicant && $this->co_applicant_enabled) {
            return $this->co_applicant_id;
        }

        return $this->primary_applicant_id;
    }

    public function getAchApplicant()
    {
        if (!$this->_achApplicant) {
            if ($this->ach_is_non_primary_applicant && $this->co_applicant_enabled) {
                $this->_achApplicant = $this->coApplicant;
            } else {
                $this->_achApplicant = $this->primaryApplicant;
            }
        }

        return $this->_achApplicant;
    }

    /**
     * @return bool
     */
    public function getStatusClass()
    {
        //TODO это для view, перенести куда-нибудь в бекенд
        $tags = [
            self::STATUS_READY_TO_PITCH => 'tag-success',
            self::STATUS_READY_TO_PITCH_NOT_SHOW => 'tag-success',
            self::STATUS_OVERDUE => 'tag-danger',
            self::STATUS_DOCS_SENT => 'tag-success',
            self::STATUS_LEAD_NEW => 'tag-warning',
            self::STATUS_CALLBACK => 'tag-info',
            self::STATUS_LEAD_HOT => 'tag-danger',
            self::STATUS_NURTURED => 'tag-danger',

            self::STATUS_DEAL_NEW => 'tag-warning',
            self::STATUS_WAITING_FIRST_PAYMENT => 'tag-info',
            self::STATUS_ACTIVE => 'tag-success',
            self::STATUS_NSF => 'tag-primary',
            self::STATUS_NSF_UNRESPONSIVE => 'tag-primary',
            self::STATUS_SUSPENDED => 'tag-default',
            self::STATUS_PENDING_CANCELLATION => 'tag-danger',
            self::STATUS_CANCELLED => 'tag-danger',
            self::STATUS_COMPLETED => 'tag-dark'
        ];

        return isset($tags[$this->status]) ? $tags[$this->status] : 'tag-default';
    }

    /**
     * @param integer $short
     * @return string
     */
    public function getDescription4Log()
    {
        return "{$this->typeText} #{$this->id} {$this->statusText} {$this->name}";
    }

    /**
     * @return string
     */
    public function getDesc4Log()
    {
        return "{$this->typeText} #{$this->id}";
    }

    /**
     * @return string
     */
    public function getAchFirstDraftAmount()
    {
        return $this->initial_payment_amount;
    }

    /**
     * @return string|boolean link to access to client portal client
     */
    public function getClientPortalLoginLink()
    {
        $ts = time();
        $token = sha1(Yii::$app->user->id . $this->id . $ts . \Yii::$app->params['credit9']['internal_login']['salt']);
        $protocol = \Yii::$app->params['credit9']['internal_login']['protocol'];
        $url = \Yii::$app->params['credit9']['internal_login']['url'];

        return isset($url) ? Url::to([
            "$protocol://$url",
            'cid' => $this->id,
            'u' => Yii::$app->user->id,
            't' => $token,
            'ts' => $ts
        ], $protocol) : false;
    }

    /**
     * @return array
     */
    public function getActiveCustomerCreditorsList()
    {
        $models = ArrayHelper::map($this->activeCustomerCreditors,'id', 'actual_name');
        natsort($models);
        return $models;
    }

    /**
     * @param $date
     * @return array|Customer|int|null|ActiveRecord
     */
    public function getFileStatusOnDate($date)
    {
        //TODO оптимизировать
        /** @var History $history */
        $history = History::find()
            ->andWhere([
                'and',
                ['customer_id' => $this->id],
                ['history.event' => History::EVENT_CUSTOMER_CHANGE_FILE_STATUS],
                ['<', 'history.ins_ts', $date],
            ])
            ->orderBy('history.ins_ts DESC')
            ->one();

        if ($history) {
            return self::getFileStatusTextByStatus($history->getDetailNewValue('file_status'));
        }

        $history = History::find()
            ->andWhere([
                'and',
                ['customer_id' => $this->id],
                ['history.event' => History::EVENT_CUSTOMER_CHANGE_FILE_STATUS],
                ['>', 'history.ins_ts', $date],
            ])
            ->orderBy('history.ins_ts ASC')
            ->one();

        if ($history) {
            return self::getFileStatusTextByStatus($history->getDetailOldValue('file_status'));
        }

        return $this->getFileStatusText();
    }

    /**
     * Send push notification by all customer devices
     * @param $title
     * @param $text
     * @return bool
     * @throws \yii\db\Exception
     */
    public function sendPushNotification($title, $text)
    {
        if (!$this->tokens) {

            return false;
        }

        $push = new PushNotification();
        $push->customer_id = $this->id;
        $push->title = $title;
        $push->text = $text;

        return $push->send();
    }

    /**
     * @return array
     */
    public static function getTotalDebtFilterItems()
    {
        return [
            '10000-20000' => '10-20k',
            '20000-40000' => '20k – 40k',
            '40000-70000' => '40k – 70k',
            '71000-100000' => '71k – 100k',
            '100000' => '100k+',
        ];
    }

    /**
     * @return bool
     */
    public function isLoanAvailable()
    {
        return $this->primaryApplicant->isLoanAvailable() || $this->override_exception_loan;
    }

    /**
     * @param bool $useActiveTime
     * @return bool
     */
    public function disabledSMS($useActiveTime = true)
    {
        return !$this->is_enabled_contacts || !$this->is_enabled_sms || ($useActiveTime && !$this->primaryApplicant->isActiveTime);
    }

    /**
     * @param bool $useActiveTime
     * @return bool
     */
    public function disabledCall($useActiveTime = true)
    {
        return !$this->is_enabled_contacts || ($useActiveTime && !$this->primaryApplicant->isActiveTime);
    }

    /**
     * @return bool
     */
    public function disabledEmail()
    {
        return !$this->is_enabled_contacts;
    }
}
<?php

namespace App\Support;

class TenantPortalNoticeContent
{
    public static function title(): string
    {
        return 'Tenant Portal Notice';
    }

    public static function version(): string
    {
        return '1.0';
    }

    /**
     * @return array<int, array{heading: string, body: string}>
     */
    public static function sections(): array
    {
        return [
            [
                'heading' => 'Purpose of the Tenant Portal',
                'body' => 'This portal is provided so that you can view information related to your tenancy, communicate with your landlord or property manager, submit maintenance requests, access selected documents, and review rent and payment records. It is an operational tool. It does not replace your written lease or tenancy agreement.',
            ],
            [
                'heading' => 'Tenant responsibilities',
                'body' => 'You are responsible for using the portal carefully and only for your own tenancy. Please keep your contact details reasonably up to date, review notices posted for you, and raise genuine issues in a timely way. Information you submit should be accurate to the best of your knowledge. Misuse of the portal, including submitting false information, harassing others, or attempting to access another person’s account, is not permitted.',
            ],
            [
                'heading' => 'Electronic communications',
                'body' => 'By using this portal, you understand that your landlord or property manager may send you tenancy-related messages through the portal, and in some cases by email linked to your account. These communications may include rent reminders, maintenance updates, notices, and document availability alerts. This does not prevent communication by other lawful means where required or agreed. You should check the portal and your email regularly.',
            ],
            [
                'heading' => 'Online rent and payment records',
                'body' => 'The portal may display amounts due, amounts recorded as paid, payment dates, methods, references, and related notes or proof uploaded by your landlord. These records are provided to help you follow your account. They are a record of what has been entered in the system. They do not, by themselves, change the rent, due dates, or payment terms in your lease. If you believe a record is incorrect, contact your landlord promptly through the portal or the contact details in your lease.',
            ],
            [
                'heading' => 'Maintenance requests and communications',
                'body' => 'You may submit maintenance requests and related comments or photos through the portal. Please describe issues clearly and in good faith. Submitting a request does not guarantee a particular repair time unless your lease or applicable law says otherwise. Status updates shown in the portal reflect information entered by your landlord or property manager. Emergency hazards should still be reported immediately using the emergency contacts in your lease or local emergency services where appropriate.',
            ],
            [
                'heading' => 'Electronic documents and records',
                'body' => 'Selected documents, notices, receipts, and other records may be stored and made available to you electronically through the portal. You should download or keep copies of documents that are important to you. Availability in the portal does not mean a document has been served on you for legal purposes unless applicable law or your lease says electronic delivery is sufficient. Your written lease remains the primary tenancy document unless a legally valid electronic signing process is used separately.',
            ],
            [
                'heading' => 'Privacy and personal information',
                'body' => 'The portal stores personal information needed to operate your tenant account, such as your name, contact details, tenancy details, payment records, maintenance history, and documents linked to your tenancy. This information is used to manage the rental relationship and to provide portal features. It is visible to authorised users in your landlord’s organisation as needed to operate the property. Do not upload sensitive information that is not needed for your tenancy. If you have questions about how your information is used, contact your landlord.',
            ],
            [
                'heading' => 'Account and login security',
                'body' => 'You must keep your login details confidential and use a strong password. Do not share your account. If you believe someone else has used your account, change your password and tell your landlord as soon as you can. You are responsible for activity carried out while you are logged in, except where you have promptly reported unauthorised access. Log out after using a shared or public device.',
            ],
            [
                'heading' => 'Portal usage rules',
                'body' => 'Use the portal only for lawful, tenancy-related purposes. Do not attempt to disrupt the service, probe other accounts, upload harmful files, or post abusive or unlawful content. Your landlord may suspend portal access if these rules are broken. Suspension of portal access does not, by itself, end your tenancy or remove rights or duties under your lease or applicable law.',
            ],
            [
                'heading' => 'No automatic modification of the tenancy or lease agreement',
                'body' => 'This notice explains how the tenant portal is used. It is not a lease, licence to occupy, or variation of your tenancy agreement. Reading and acknowledging this notice does not create a new tenancy, change rent, change the term, add or remove occupants, or waive any rights. If your lease needs to be changed, that must be done through a separate, valid agreement in line with your lease and applicable law.',
            ],
            [
                'heading' => 'Applicable law and jurisdiction',
                'body' => 'Your tenancy continues to be governed by your written lease and by the residential tenancy laws that apply to the property. This portal notice is intended to describe portal use only. Nothing in this notice limits any non-excludable rights you have under applicable consumer or tenancy law. Disputes about the tenancy should be handled according to your lease and the law of the place where the property is located, unless a court or tribunal requires otherwise.',
            ],
            [
                'heading' => 'Support and contact information',
                'body' => 'If you cannot access the portal, believe a record is wrong, or need help using a feature, contact your landlord or property manager using the details in your lease or the contact information shown in this portal. For issues that are urgent for health or safety, follow the emergency instructions in your lease and contact emergency services if required. Technical problems with login should be reported to your landlord so they can restore access where appropriate.',
            ],
        ];
    }
}

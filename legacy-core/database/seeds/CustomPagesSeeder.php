<?php

use App\Models\CustomPage;
use Illuminate\Database\Seeder;

class CustomPagesSeeder extends DatabaseSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        CustomPage::truncate();
        DB::beginTransaction();
            CustomPage::create([
                'title' => 'Terms and Conditions',
                'slug' => 'terms',
                'content' => '## Terms & Privacy Policy

PLEASE READ THE FOLLOWING TERMS AND CONDITIONS OF USE CAREFULLY BEFORE USING THIS WEBSITE. All users of this site agree that access to and use of this site are subject to the following terms and conditions and other applicable law. If you do not agree to these terms and conditions, please do not use this site.

### Copyright

The entire content included in this site, including but not limited to text, graphics or code is copyrighted as a collective work under the United States and other copyright laws, and is the property of ControlPad. The collective work includes works that are licensed to ControlPad. Copyright 2003, ControlPad – ALL RIGHTS RESERVED. Permission is granted to electronically copy and print hard copy portions of this site for the sole purpose of placing an order with ControlPad or purchasing ControlPad products. You may display and, subject to any expressly stated restrictions or limitations relating to specific material, download or print portions of the material from the different areas of the site solely for your own non-commercial use, or to place an order with ControlPad or to purchase ControlPad products. Any other use, including but not limited to the reproduction, distribution, display or transmission of the content of this site is strictly prohibited, unless authorized by ControlPad. You further agree not to change or delete any proprietary notices from materials downloaded from the site.

### Trademarks

All trademarks, service marks and trade names of ControlPad. ControlPad used in the site are trademarks or registered trademarks of ControlPad.

### Warranty Disclaimer

This site and the materials and products on this site are provided "as is" and without warranties of any kind, whether express or implied. To the fullest extent permissible pursuant to applicable law, ControlPad disclaims all warranties, express or implied, including, but not limited to, implied warranties of merchantability and fitness for a particular purpose and non-infringement. ControlPad does not represent or warrant that the functions contained in the site will be uninterrupted or error-free, that the defects will be corrected, or that this site or the server that makes the site available are free of viruses or other harmful components. ControlPad does not make any warrantees or representations regarding the use of the materials in this site in terms of their correctness, accuracy, adequacy, usefulness, timeliness, reliability or otherwise. Some states do not permit limitations or exclusions on warranties, so the above limitations may not apply to you.

### Limitation of Liability

ControlPad shall not be liable for any special or consequential damages that result from the use of, or the inability to use, the materials on this site or the performance of the products, even if ControlPad has been advised of the possibility of such damages. Applicable law may not allow the limitation of exclusion of liability or incidental or consequential damages, so the above limitation or exclusion may not apply to you.

### Typographical Errors

In the event that a ControlPad product is mistakenly listed at an incorrect price, ControlPad reserves the right to refuse or cancel any orders placed for product listed at the incorrect price. ControlPad reserves the right to refuse or cancel any such orders whether or not the order has been confirmed and your credit card charged. If your credit card has already been charged for the purchase and your order is cancelled, ControlPad shall issue a credit to your credit card account in the amount of the incorrect price.

### Term; Termination

These terms and conditions are applicable to you upon your accessing the site and/or completing the registration or shopping process. These terms and conditions, or any part of them, may be terminated by ControlPad without notice at any time, for any reason. The provisions relating to Copyrights, Trademark, Disclaimer, Limitation of Liability, Indemnification and Miscellaneous, shall survive any termination.

### Notice

ControlPad may deliver notice to you by means of e-mail, a general notice on the site, or by other reliable method to the address you have provided to ControlPad

### Miscellaneous

Your use of this site shall be governed in all respects by the laws of the State of Utah, U.S.A., without regard to choice of law provisions, and not by the 1980 U.N. Convention on contracts for the international sale of goods. You agree that jurisdiction over and venue in any legal proceeding directly or indirectly arising out of or relating to this site (including but not limited to the purchase of ControlPad products) shall be in the state or federal courts located in Salt Lake County, Utah. Any cause of action or claim you may have with respect to the site (including but not limited to the purchase of ControlPad products) must be commenced within one (1) year after the claim or cause of action arises. [company_nam]hall not be construed as a waiver of any provision or right. Neither the course of conduct between the parties nor trade practice shall act to modify any of these terms and conditions. ControlPad may assign its rights and duties under this Agreement to any party at any time without notice to you.

### Use of Site

Harassment in any manner or form on the site, including via e-mail, chat, or by use of obscene or abusive language, is strictly forbidden. Impersonation of others, including a ControlPad or other licensed employee, host, or representative, as well as other members or visitors on the site is prohibited. You may not upload to, distribute, or otherwise publish through the site any content which is libelous, defamatory, obscene, threatening, invasive of privacy or publicity rights, abusive, illegal, or otherwise objectionable which may constitute or encourage a criminal offense, violate the rights of any party or which may otherwise give rise to liability or violate any law. You may not upload commercial content on the site or use the site to solicit others to join or become members of any other commercial online service or other organization.

### Participation Disclaimer

ControlPad does not and cannot review all communications and materials posted to or created by users accessing the site, and is not in any manner responsible for the content of these communications and materials. You acknowledge that by providing you with the ability to view and distribute user-generated content on the site, ControlPad, is merely acting as a passive conduit for such distribution and is not undertaking any obligation or liability relating to any contents or activities on the site. However, ControlPad reserves the right to block or remove communications or materials that it determines to be (a) abusive, defamatory, or obscene, (b) fraudulent, deceptive, or misleading, (c) in violation of a copyright, trademark or; other intellectual property right of another or (d) offensive or otherwise unacceptable to ControlPad in its sole discretion.

### Indemnification

You agree to indemnify, defend, and hold harmless ControlPad, its officers, directors, employees, agents, licensors and suppliers (collectively the "Service Providers") from and against all losses, expenses, damages and costs, including reasonable attorneys’ fees, resulting from any violation of these terms and conditions or any activity related to your account (including negligent or wrongful conduct) by you or any other person accessing the site using your Internet account.

### Third-Party Links

In an attempt to provide increased value to our visitors, ControlPad may link to sites operated by third parties. However, even if the third party is affiliated with ControlPad, ControlPad has no control over these linked sites, all of which have separate privacy and data collection practices, independent of ControlPad. These linked sites are only for your convenience and therefore you access them at your own risk. Nonetheless, ControlPad seeks to protect the integrity of its web site and the links placed upon it and therefore requests any feedback on not only its own site, but for sites it links to as well (including if a specific link does not work).

### LICENSE AGREEMENT

IMPORTANT NOTICE: CONTROLPAD IS WILLING TO MAKE THE SERVICE AVAILABLE TO YOU ONLY IF YOU ACCEPT THE TERMS AND CONDITIONS IN THIS AGREEMENT.

This License Agreement (“Agreement”) is a legal agreement between Controlpad, LLC, a Utah limited liability company (“Controlpad”), and you, a legal entity for which an account was set up under which this Agreement was accessed (“End-user,” “you” or “your”).

If you wish to license the Service from Controlpad, you must click on the “I accept the terms and conditions of this Agreement” button. By clicking the “I accept the terms and conditions of this Agreement” button you acknowledge: (a) that you have read and understood this Agreement; and (b) that this Agreement has the same force and effect as an agreement signed with original signatures. If you do not click on the “I accept the terms and conditions of this Agreement” button you will not be able to use the Service. You warrant that you have full authority to accept and perform this Agreement.

This Agreement includes a disclaimer of warranties, a disclaimer of liability, as well as a release and indemnification by you, in Sections 9, 10, 11\. Please review those sections (and all other terms) carefully.

1.  **Service.**     Controlpad is in the business of providing a Backoffice and Support Platform to businesses in the direct sales and social commerce industries. Subject to the terms and conditions of this Agreement and by virtue of the Master Agreement (defined below), during the Term, Controlpad will provide End-user with the Service on a hosted basis. The “Service” means the Controlpad Backoffice & Support Platform, a software as a service platform designed for direct sales and social commerce businesses to manage memberships, affiliates, and facilities utilizing cloud-based web and mobile applications to streamline contact management and billing solutions, together with any additional features or functionality developed by Controlpad that augment or enhance such platform, including any updates thereto provided as part of the Service, and the documentation therefor. Controlpad may update the content, functionality, and user interface of the Service from time to time in its sole discretion.

2.  **License.**

    *   2.1.      **License Grant.**     Subject to the terms and conditions of this Agreement, Controlpad grants End-user, during the Term of this Agreement, a non-exclusive, non-transferable and terminable license to use the Service solely for End-user’s internal business operations, provided such operations shall not include service bureau use, outsourcing, renting, or time-sharing the Service. End-user may access the Service either via (i) the domains that allow End-user and its permitted users to access the Service from the supported browsers including Mycontrolpad.com (the “**Platform Website**”) or (ii) a software application that may be downloaded through Controlpad or through Controlpad-approved software platforms or markets, from time to time, such as Apple’s App Store, and loaded onto a Device by End-user or any of its permitted users (the “**Controlpad Mobile Application**”). “**Device**” means an iPhone, iPad, PDA, mobile or other hand-held devices on which the then-current Controlpad Mobile Application is permitted to run. End-user acknowledges that a Controlpad Mobile Application will not be usable unless and until the permitted user and applicable Device are registered as required by Controlpad. The functionality of the Service may vary based on whether the Service is accessed via the Platform Website or via the Controlpad Mobile Application.

    *   2.2.      **Other Governing Documents.**     Your access to the Service is obtained through an entity that has entered into a separate License Agreement with Controlpad (the “**Master Agreement**”). You acknowledge and agree that your access to and use of the Service is further subject to the terms and conditions of the Master Agreement. Your access to and use of the Service is also subject to any terms of use, acceptable use policies, end user license agreements and other guidelines established by Controlpad or its licensors and posted to the Backoffice and Support Platform.

    *   2.3.     **Restrictions.**     End-user acknowledges and agrees that the rights granted to End-user hereunder are provided to End-user on the condition that End-user does not (and does not allow any third party to) copy, recreate, display, perform, reproduce, replicate, frame, mirror, publish, modify, create a derivative work of, reverse engineer, reverse assemble, disassemble, or decompile the Service or any part thereof or otherwise attempt to discover any source code, modify the Service in any manner or form, or use unauthorized modified versions of the Service, including (without limitation) for the purpose of building a similar or competitive product or service or for the purpose of obtaining unauthorized access to the Service, or merging the Service with any other software. End-user is expressly prohibited from sublicensing use of the Service to any third parties. Except as provided in this Agreement, the license granted to End-user does not convey any rights in the Service, express or implied, or ownership in the Service or any intellectual property rights thereto. Any rights not expressly granted herein are reserved by Controlpad.

    *   2.4.     **Permitted Users.**     End-user may designate an unlimited number of its employees as permitted users of the Service under End-user’s account, provided that the use of the Service by such users shall be solely for End-user’s internal business operations. End-user will ensure that any use of the Service by End-user’s employees is in accordance with the terms and conditions of this Agreement.

    *   2.5.     **Maintenance.**     Controlpad may conduct maintenance and upgrades, or issue new releases, which may cause the Service to be temporarily unavailable.

3.  **License from End-user.**     End-user hereby grants Controlpad the non-exclusive, non-transferable (except in connection with an assignment under Section 12 herein) license to copy, store, host, record, transmit, maintain, display, view, print, or otherwise use any data, information, or other materials of any nature whatsoever, provided to Controlpad by End-user in the course of implementing and/or using the Service (“**End-user Data**”) to the extent necessary to provide the Service to End-user. The foregoing license is sublicensable to Controlpad’s subcontractors subject to a written agreement containing terms substantially similar to these. End-user agrees that the license to End-user Data shall survive the termination of this Agreement for six months, for the purposes of storing backup End-user Data at an offsite storage facility. Controlpad may include End-user’s trade names, trademarks, service marks, logos, domain names, and other distinctive brand features in presentations (collectively, “**End-user Marks**”), marketing materials, and customer lists, subject to reasonable trademark practices and guidelines provided by End-user to Controlpad in writing. Upon End-user’s request, Controlpad will furnish End-user with a sample of such usage.

4.  **Terms of Service.**     End-user’s access to the Service is contingent upon End-user’s compliance with the following:

    *   4.1.     **Accuracy of End-user’s Registration Information.**     End-user shall provide accurate, current and complete information (“**Registration Data**”) about End-user and its permitted users as required by Controlpad for its provision of the Service. End-user further agrees to use commercially reasonable efforts to maintain and promptly update the Registration Data to keep it accurate, current and complete. End-user acknowledges and agrees that if End-user provides information that is intentionally inaccurate, not current or incomplete in a material way, or if Controlpad has reasonable grounds to believe that such information is untrue, inaccurate, not current, or incomplete in a material way, Controlpad has the right to suspend End-user’s account.

    *   4.2.     **Email Notices.**     End-user agrees that Controlpad may provide any and all notices, statements, and other communications to End-user through either e-mail at the email address provided as part of the Registration Data or by mail or express delivery service. Any notices from End-user to Controlpad shall be delivered to info@controlpad.com

    *   4.3.     **Passwords, Access, and Notification.**     End-user shall provide and assign unique passwords and user names to each permitted user. End-user will be responsible for the confidentiality and use of End-user’s and its users’ passwords and user names. Controlpad may assume that any electronic communications it receives under End-user’s passwords, user name, and/or account number will have been sent by End-user. End-user agrees to immediately notify Controlpad if End-user becomes aware of any loss or theft or unauthorized use of any of End-user’s passwords, user names, and/or account number. End-user further shall immediately notify Controlpad if a user leaves the employment or service of End-user, and shall take such action as may be required to terminate such user’s access to the Service.

    *   4.4.     **End-user’s Lawful Conduct.**     End-user is solely responsible for the content of any postings, data, or transmissions using the Service, or any other use of the Service by End-user. End-user shall comply with all applicable local, state, federal, and foreign laws, treaties, regulations, and conventions in connection with its use of the Service, including without limitation those related to privacy, electronic communications, and anti-spam legislation. End-user has and will maintain any permission from third parties that may be required in order to provide and make available any End-user Data for use as contemplated hereunder. End-user shall also comply with any written policies or procedures developed by Controlpad from time to time, which shall be made available to End-user by Controlpad, regarding the use of the Service, including without limitation any policies that govern what types of content may or may not be uploaded through the Service. End-user will not upload, post, reproduce or distribute any information, software or other material protected by copyright or any other intellectual property right (including rights of publicity and privacy) without first obtaining the permission of the owner of such rights.

    *   4.5.      **Transmission of Data.**     End-user acknowledges and understands that electronic communications and data may be accessed by unauthorized parties when communicated across the Internet, network communications facilities, telephone, or other electronic means. End-user agrees that Controlpad is not responsible for any End-user Data or Registration Data which is lost, altered, intercepted or stored without authorization during the transmission of any data whatsoever across networks not owned and/or operated by Controlpad. To the extent deemed necessary by End-user, End-user shall implement security procedures necessary to limit access to the Service to End-user’s authorized users and shall maintain a procedure external to the Service for reconstruction of lost or altered files, data or programs. End-user is responsible for establishing designated points of contact to interface with Controlpad.

    *   4.6.     **Trademark Information.**     Controlpad service marks, logos and product and service names are marks of Controlpad (the "Controlpad Marks"). End-user agrees not to display or use the Controlpad Marks in any manner without Controlpad\'s express prior written permission.

5.  **Confidential Information.**     or purposes of this Agreement, confidential information shall include the terms of this Agreement and any proprietary or confidential information of either party (“**Confidential Information**”). Controlpad’s Confidential Information shall include the Service and all of its content. Each party agrees: (a) to keep confidential all Confidential Information disclosed to it by the other party using no less than reasonable efforts; (b) not to use the Confidential Information of the other party except to the extent necessary to perform its obligations hereunder; and (c) to protect the confidentiality thereof in the same manner as it protects the confidentiality of similar information and data of its own (at all times exercising at least a reasonable degree of care in the protection of such Confidential Information). Controlpad may disclose Confidential Information to its employees and contractors which have executed written agreements requiring them to maintain the confidentiality of such information in order to facilitate the performance of their services for Controlpad in connection with the performance of this Agreement. Confidential Information shall not include information which: (1) is known publicly; (2) is generally known in the industry before disclosure; (3) has become known publicly, without fault of the recipient, subsequent to disclosure by the disclosing party; or (4) the recipient becomes aware of from a third party not bound by non-disclosure obligations to the disclosing party and with the lawful right to disclose such information to the recipient. This Section 5 will not be construed to prohibit the disclosure of Confidential Information to the extent that such disclosure is required by law or order of a court or other governmental authority. The parties agree to give the other party prompt notice of the receipt of any subpoena or other similar request for such disclosure.

6.  **Ownership of the Software.**     End-user agrees that Controlpad shall retain ownership of all right, title and interest (including all copyrights, moral rights, trademarks, trade names, patents and other intellectual property rights) in and to the Service (including without limitation all of the Controlpad software, documentation, updates, improvements, enhancements, derivative works and other such items (but excluding the End-user Data)), and in the software, hardware, other materials, processes, know-how and the like utilized by or created by Controlpad in the provision of the Service, subject to the limited licenses granted to End-user hereunder during the term of this Agreement. End-user hereby assigns to Controlpad any right, title or interest that End-user may acquire during the Term of this Agreement in and to the Service or the software, hardware, other materials, processes, know-how and other such intellectual property associated therewith. In addition, Controlpad shall have a royalty-free, transferable, sublicensable, irrevocable, perpetual, nonexclusive license to use or incorporate in the Service any suggestion, enhancement, recommendations or other feedback provided by End-user or its users relating to the operation, features or functionality of the Service. Controlpad agrees that End-user shall retain ownership of all right, title and interest in and to the End-user Data, subject to the limited licenses granted to Controlpad hereunder during the Term of this Agreement and for the period thereafter specified above.

7.  **Term; Suspension/Termination.**

    *   7.1.      **Term.**     The term of this Agreement shall be perpetual, except that either party may terminate this Agreement for any reason upon ninety (90) days written notice of termination to the other party (the “**Term**”). This Agreement shall terminate automatically upon the expiration or other termination of the Master Agreement.

    *   7.2.      **Suspension for Ongoing Harm.**     In the event that Controlpad believes that End-user’s or any of its users’ use of the Service is causing harm to Controlpad or the Service, Controlpad may immediately suspend End-user’s or such user’s access until the issue(s) are resolved. Controlpad agrees to re-activate any suspended End-user account upon resolution of the issue that prompted suspension.

    *   7.3.      **In the Event of Breach.**     Either party may terminate this Agreement at any time with written notice to the other party in the event of a breach of any material provision of this Agreement by the other party, provided that the breaching party fails to cure such breach within 14 days after receipt of written notice.

    *   7.4.      **Effect of Termination.**Upon termination or expiration of this Agreement, End-user’s right to use the Service shall immediately cease. Upon termination or expiration of this Agreement, each party shall promptly return or destroy the other party’s Confidential Information.

    *   7.5.      **Release.**End-user agrees that Controlpad shall not be liable to End-user or to any third party for any termination of End-user access to the Service or deletion of End-user Data pursuant to this Section 7.

8.  **Modification to the Service.**     Controlpad reserves the right at any time and from time to time to modify, temporarily or permanently, the Service or any component or feature thereof. End-user agrees that Controlpad shall not be liable to End-user or to any third party for any modification of the Service as described in this Section 8.

9.  **Disclaimer of Warranties.**     CONTROLPAD DISCLAIMS ALL WARRANTIES AND CONDITIONS, EXPRESS AND IMPLIED, WITH RESPECT TO THE SERVICE AND THIS AGREEMENT, INCLUDING WITHOUT LIMITATION, THOSE OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NON-INFRINGEMENT, AND THOSE ARISING FROM COURSE OF DEALING, COURSE OF PERFORMANCE AND TRADE USAGE. THE SERVICE IS PROVIDED TO END-USER ON AN “AS IS” AND “AS AVAILABLE” BASIS, AND IS FOR COMMERCIAL USE ONLY. CONTROLPAD DOES NOT REPRESENT THAT END-USER’S USE OF THE SERVICE WILL BE TIMELY, UNINTERRUPTED OR ERROR-FREE OR THAT THE SERVICE WILL MEET END-USER’S REQUIREMENTS OR THAT ALL ERRORS IN THE SERVICE AND/OR DOCUMENTATION WILL BE CORRECTED OR THAT THE SYSTEM THAT MAKES THE SERVICE AVAILABLE WILL BE FREE OF VIRUSES OR OTHER HARMFUL COMPONENTS. END-USER ASSUMES ALL RESPONSIBILITY FOR DETERMINING WHETHER THE SERVICE IS ACCURATE OR SUFFICIENT FOR END-USER’S PURPOSES.

10.  **Limitations of Liability.**     END-USER ACKNOWLEDGES AND AGREES THAT THE CONSIDERATION WHICH CONTROLPAD IS CHARGING UNDER THE MASTER AGREEMENT DOES NOT INCLUDE CONSIDERATION FOR ASSUMPTION BY CONTROLPAD OF THE RISK OF END-USER’S INCIDENTAL OR CONSEQUENTIAL DAMAGES. IN NO EVENT SHALL CONTROLPAD BE LIABLE UNDER OR RELATING TO THIS AGREEMENT FOR INCIDENTAL, CONSEQUENTIAL, PUNITIVE, SPECIAL OR EXEMPLARY DAMAGES, OR INDIRECT DAMAGES OF ANY TYPE OR KIND (INCLUDING LOSS OF END-USER DATA, REVENUE, PROFITS, USE OR OTHER ECONOMIC ADVANTAGE), ARISING FROM BREACH OF WARRANTY OR BREACH OF CONTRACT, OR NEGLIGENCE, OR ANY OTHER LEGAL CAUSE OF ACTION ARISING FROM OR IN CONNECTION WITH THIS AGREEMENT EVEN IF IT WAS ADVISED OF THE POSSIBILITY OF THE FOREGOING. The maximum liability of Controlpad to End-user or any other any person, firm or corporation whatsoever arising out of or in the connection with any license, use or other employment of the Service or this Agreement, whether such liability arises from any claim based on breach or repudiation of contract, breach of warranty, tort, or otherwise, shall in no case exceed $500.00\. The essential purpose of this provision is to limit the potential liability of the parties arising from this Agreement. The parties acknowledge that the limitations set forth in this Section are integral to the amount of consideration levied in connection with the license of the Service and that, were Controlpad to assume any further liability other than as set forth herein, such consideration would of necessity be set substantially higher.

11.  **Indemnification.**     End-user shall indemnify, defend and hold Controlpad harmless from and against any and all costs, losses, and expenses (including, but not limited to, reasonable attorneys’ fees) incurred by Controlpad arising out of or in connection with any claim, suit, action, or proceeding brought by any third party against Controlpad (i) alleging that the End-user Data, Registration Data or any End-user Marks, or any use thereof, infringes the intellectual property rights or other rights, or has caused harm to a third party, or (ii) arising out of End-user’s breach of this Agreement or misuse of the Service, provided that Controlpad: (a) promptly provides End-user notice of the claim, suit, action, or proceeding (provided that the failure of the indemnified party to provide such notice shall not affect the indemnifying party’s obligations except to the extent such failure materially prejudices the indemnifying party); (b) gives End-user the right to assume sole control of the defense and related settlement negotiations if End-user gives notice to Controlpad of its intention to do so within thirty (30) days after receipt of notice of the claim; and (c) provides End-user with all reasonably available information and assistance necessary to perform End-user’s obligations under this paragraph.

12.  **Miscellaneous.**     This Agreement shall inure to benefit and bind the parties hereto, their successors and assigns, but End-user may not assign this Agreement or the license without the written consent of Controlpad. This Agreement does not create any joint venture, partnership, agency, or employment relationship between the parties, although Controlpad reserves the right to name End-user as a user of the Service. This Agreement represent the entire agreement of the parties and supersede all prior and contemporaneous discussions and/or agreements between the parties and is intended to be the final expression of their Agreement. It shall not be modified or amended except in writing signed by both parties. This Agreement shall be governed in accordance with the laws of the State of Utah and any controlling U.S. federal law, without regard to conflict of law principles. Any disputes, actions, claims or causes of action arising out of or in connection with this Agreement (or the Service) shall be subject to the exclusive jurisdiction of the state and federal courts located in Salt Lake County, Utah. If any provision is held by a court of competent jurisdiction to be contrary to law, such provision shall be limited or eliminated to the minimum extent necessary so that this Agreement shall otherwise remain in full force and effect. Neither party shall be liable for any loss or delay resulting from any “Force Majeure Event,” which is defined as including, but not being limited to, acts of God, fire, natural disaster, terrorism, labor stoppage, war or military hostilities, criminal acts of third parties. Any payment date or delivery of Service date shall be extended to the extent of any delay resulting from any Force Majeure Event. Sections 2.2, 3 (for the term specified therein), 4.6, 5, 6, 7.4, 7.5, 9, 10, 11, and 12 shall survive the termination or expiration of this Agreement.'
            ]);
            CustomPage::create([
                'title' => 'FAQ',
                'slug' => 'faq',
                'content' => '### Frequently asked questions should go here.'
            ]);
            CustomPage::create([
                'title' => 'Privacy Policy',
                'slug' => 'privacy',
                'content' => 'We gather and utilize various types of information listed below. By using this Site, you signify your acceptance of this Policy.

Note that this Policy applies only with respect to the information collected by Controlpad and not any information collected or obtained through other methods or sources.

### Public

Definition
Public information will include that information available for registered members to view, or demographic information. This includes your demographic information such as gender, name, age, birth date, purchase history information, browsing history information, appearance, zip code and geographical location or any other information deemed necessary by Controlpad.

### Private

Private information is information that can personally identify you such as your real name, email address or credit card number.

When you register to use any Site service, you will be asked for some general identifying information to enable us to provide you with this individualized service. This information may include email addresses, personal information and credit card information. Credit card transactions may be processed by the Site via its third party intermediary/financial institution, or by a professional payment authorization service chosen for security and reliability. In either case, only the purchase price and the credit card information provided by the customer will be supplied, and such information will be maintained in their database for accounting and billing purposes. This information is encrypted via a secure server to ensure that others cannot read it. Once on the secure server your credit card information is sent to a third party intermediary for processing. We are currently using a trusted credit card processing system and an SSL security certificate.

### Automatic

Automatic information is information that we collect that is not personally identifiable such as your IP address, site usage and purchase behavior, browser type, browser activity, and browser settings.

We use your IP and/or Mac address to avoid fraudulent registrations, and monitor site usage demographics and dynamics. Information that we may obtain regarding your browser and browser settings may be used to ensure that our site’s technical features are available to you. Except as permitted herein, the specific information we collect is not rented, sold, or shared with any third party without your explicit consent and is only ever disclosed in situations where required by law.

### Sharing of Information

While data from the member sites is maintained in a common database, only the public information located in your profile will be shared. Private information such as your real name, email address or credit card number is never shared without your explicit consent. Controlpad adheres to a consistent set of privacy policies and therefore follow the same principles outlined in this privacy policy.

The public demographic information contained in your profile may be presented and/or displayed, however at no time will your private information be disclosed or presented except in accordance with the provisions of this policy.

Except as permitted herein, Controlpad does not rent, sell, or share personal information about you with any third party, except to the third party intermediary or financial institution that processes your transaction or where required by law. However, we may use demographic information to tailor the Site and communications to your interests, and we may share demographic information with advertisers or other third parties on an anonymous and aggregated basis such that individual private information or behavior may not be determined (i.e., without telling the advertisers your identity). Our sharing of demographic information is anonymous (i.e., we do not tell which particular users are members of which demographic groups), subject to the rest of this privacy policy. When you respond to an advertisement and give the advertiser your personal information, then the advertiser may be able to identify you as being a member of that demographic group.

We also may disclose your personal information or financial information to our subsidiary and parent companies and businesses, and other affiliated legal entities and businesses with whom we are under common corporate control. Whenever personal information or financial information is disclosed under this paragraph we may also disclose your demographic information, on a non-anonymous basis. All of our parent, subsidiary and affiliated legal entities and businesses that receive your personal information, financial information, or non-anonymous demographic information from us will comply with the terms of this privacy policy with respect to their use and disclosure of such information.

To operate the Site, including processing your transactions and supporting your activities on the Site, we may share your personal information with our agents, representatives, contractors and service providers so they can provide us with support services such as authorization of credit card transactions, email origination, receipt or support services, customer relationship management services, order fulfillment and sweepstakes and promotional fulfillment. By purchasing, or registering or making reservations for, products or services offered or sponsored by third parties on the Site, or electing to receive communications (such as emails or magazine subscriptions) or electing to participate in contests, sweepstakes or other programs (such as discount or rewards programs), offered or sponsored by third parties on the Site, you consent to our providing your personal information to those third parties. Those third parties may use your personal information in accordance with their own privacy policies. You will need to contact those third parties to instruct them directly regarding your preferences for the use of your personal information by them. Additionally, you agree that we may use and disclose all such information so submitted to such third parties in the same manner in which we are entitled to use and disclose any other information you submit to us.

### Cookies

In the course of serving advertisements to users in e-mails, third-party advertisers or ad servers may place or recognize a unique cookie on your browser. The use of cookies by such third party advertisers or ad servers is not subject to this Policy, but is subject to their own respective privacy policies.

### Single-Pixel Gifs

The Owner and its advertisers may use single-pixel gifs (also known as web bugs, web beacons, or tracking pixels) on the Site. The purpose of these single-pixel gifs is to verify and count web page accesses and gather some statistical information. These single-pixel gifs gather usage and demographic information, but do not gather any personal information.

### Email

To enhance your experience as a Site member, we send out message notifications, newsletters and site announcements, and from time to time third party offers from companies that we feel may have something that will benefit you. Our site provides you with the opportunity to opt-out of receiving different types of communications (except system communications, or communications required for account maintenance), and each email that we send out has a method for opting-out located at the bottom of the message.

### Opt-Out and CAN-SPAM Act

When you register on the Controlpad site, you will automatically be registered to receive promotional offers and updates, via e-mails, from thebodyevolution.com, puremusclepower.com and purewillpower.com and from the Network Sites and other associated e-mail brands.

Additionally, you may be provided with the opportunity to receive promotional offers, via e-mails, from partners of Controlpad.com. While some of these campaigns will only be sent to you if you expressly request them, others may be sent to you unless you elect not to receive them. Accordingly, please review these offers carefully.

At any time, you may choose not to receive promotional e-mails from Controlpad.com or partners of Controlpad.com by following the "unsubscribe" instructions in the applicable e-mail. Notwithstanding the foregoing, Controlpad may continue to contact customer for the purpose of facilitating, completing or confirming any transaction and/or inquiry.

Note that unsubscribing from one e-mail list does not automatically unsubscribe you from any other e-mail list that you may be on. Please read the e-mail carefully to find out which list you are unsubscribing

### Children

Unless otherwise noted, the Site, Controlpad.com e-mails and the content available in connection therewith, are neither intended for, nor directed to, children under the age of 18\. Except as otherwise noted.

### Links

Controlpad may have links to other websites on our Site. Some of these other websites contain our brand names and trademarks and other intellectual property that we own; others do not. When you click on these links and visit these other websites, regardless of whether or not they contain our brand names, trademarks and other intellectual property, you need to be aware that we do not control these other websites or these other websites’ business practices, and that this privacy policy does not apply to these other websites.

### Changes to our Privacy Policy

Controlpad – Controlpad.com may, from time to time, amend this Policy, in whole or part, in its sole discretion. Any changes to this Policy will be effective immediately upon the posting of the revised policy on the Site.

### Contact us with your questions or suggestions

If you have any questions or concerns regarding our privacy policy, please contact our Customer Care Department at 1.800.836.4493 or emailing info@controlpad.com'
            ]);
            CustomPage::create([
                'title' => 'Shipping and Returns Policy',
                'slug' => 'return-policy',
                'content' => 'An item may be returned for a refund or exchange as long as it is within 14 days of the receipt that is emailed out. The item must be in the original unused condition as originally shipped to the customer. Please include the name you used when you placed the order on all returns and you will be refunded to the card you used when placing your order. Refunds take up to 5-10 business days to hit your account. If you would like to exchange an item, please let us know what size you would like to exchange for on the card that was shipped with your items. \n'
                . ' All returns can be sent to the return address on your shipment or address on the insert card.\n'
                . ' ### Shipping

Shipping and handling charges are non-refundable. The customer is responsible for return shipping costs, unless the item has arrived damaged**. All refunds will be for the price of the item purchased and tax (if applicable), but not the cost of shipping.

**If the item arrives damaged or there is a mistake on our part (if you received the wrong item/color/size placed on your original order), you will be reimbursed for the purchase including shipping fees.

### Arrival Time

After you place an order it will be processed within 24 hours unless on a non-business day (Saturday or Sunday, in that case your order will be processed the following business day). Domestic orders can arrive in 5-10 business days, and international orders between 14-21 business days.

ControlPad is not responsible for any lost or stolen packages once delivered.

If you have any other questions concerning your order feel free to contact us at info@controplad.com.

## Privacy Policy

Effective January 1, 2016

ControlPad is committed to protecting your privacy and utilizing technology that gives you the most powerful, safe, on-line experience available. We want to assure you that when ControlPad asks for consumer information, it\'s done with the goal of improving our relationship.

The following statements will help you understand how we collect, use and safeguard information you provide to My ControlPad.

**Internet Specific Information**

**Information Collection and Use**

When you browse ControlPad website, you do so without revealing your identity. Personal information, including email address, is not collected without your awareness. For each visitor to our website, our servers recognize the website you came from.

In the order capture process, ControlPad may track the order preferences of visitors to our website. Information tracked and retained by My ControlPad is generic and not individually identifiable. This is accomplished through cookies. Cookies are small pieces of information that are stored by your browser on your computer\'s hard drive. A cookie may contain information (such as unique session ID), that is used to track the pages of the sites youve visited. We use cookies but do not store personally identifiable information in your cookies. ControlPad’s website uses cookies in order to improve your shopping experience. This information helps us serve you better by keeping track of your order as you shop at our site and it helps us by improving our site design.

Most Web browsers automatically accept cookies, but you can usually change your browser to prevent that. Even without a cookie, you can use the majority of the features within our sites. Identifying Information You Choose to Provide

At times, ControlPad may request that you voluntarily supply personal information for purposes such as correspondence, success tracking, site registration, making a purchase, participating in an on-line survey or employment opportunities. If you elect to participate, ControlPad may require personally identifiable information, including your name, mailing address, phone number, bank information, credit card information or email address. When you submit personal information to My ControlPad you understand and agree that as allowed by contract ControlPad may access, store and use your information. Information you provide will be safeguarded to strict standards of security and confidentiality. Because ControlPad recognizes and appreciates the importance of responsible use of the information you choose to provide, we do not share email addresses.

**Security of Information.** Security measures are in place on the ControlPad website to protect against the loss, misuse and alteration of the information under our control. We offer industry-standard security measures available through your web browser. When you are on our website, the information you provide is encrypted and scrambled en route and decoded once it reaches ControlPad. Please remember that email, which is different from the encrypted sessions above, is not secure. Therefore, we ask that you do not send confidential information such as social security or account numbers to us via unsecured email. Such communications should be sent to us by postal mail or phone.

ControlPad takes steps to require that its vendors, consultants, suppliers, and contractors we hire observe our Privacy Policy with respect to security and the collection, use and exchange of our consumers’ individual consumer information. They are expected to abide by the Privacy Policy when conducting work for us.

**Children Visiting our website.** Our website is directed at adult consumers and guests. However, children may access our website and provide information. They may request information concerning our products and services and attempt to order certain products and services. Should children access our website, we are confident that parents will not judge any of the information provided as objectionable for viewing. If you are under 18, you may use the ControlPad website only with the involvement of a parent or guardian.

**Other Websites Linked to ControlPad.** ControlPad is not responsible for the content or information practices employed by sites linked to or from our website. In most cases, links to non-ControlPad websites are provided solely as pointers to information on topics that may be useful to the users of the ControlPad website.

**General Business Practices** Disclosure of Individual Consumer Information. We are committed to protecting your private information. ControlPad uses the information collected from you to process orders and to provide a more personalized shopping experience. ControlPad may also use your information to communicate with you about products, services, and promotions in the future. For the purposes of check verification and fraud prevention, ControlPad will provide data about our consumers to reputable reference sources and clearinghouse services. The information we gather may be used to protect consumers, employees and property against fraud, theft or abuse; to conduct industry or consumer surveys; and to maintain good consumer relations.

ControlPad does not share email addresses, or any other information with vendors outside out company.

**Choice to Opt-Out.** ControlPad provides consumers the opportunity to opt-out of receiving unsolicited mail from ControlPad. You may opt-out by emailing us using the [“Contact Us”](/contact) tab on the home page and expressing your request. You may also make this request by any other communication option listed below under ControlPad

**Correct/Update/Access.** ControlPad is committed to ensuring that the information we obtain and use about consumers is accurate. You can help us maintain the accuracy of information previously provided or information we may have collected by informing us of changes or modifications through the [**Contact Us tab on the home page.**](/contact) ControlPad service representatives are trained to answer consumer questions, and to give consumers reasonable access to the information we have about them.

**Enforcement.** Periodically, our operations and business practices are reviewed for compliance with corporate policies and procedures governing the confidentiality of information. These reviews are conducted by internal staff, internal auditors who report directly to the Board of Advisors, external auditing and accounting firms and government regulators. Included in these self-assessments and examinations are reviews of the controls and safeguards related to consumer privacy described in our Privacy Policy.

**Privacy Policy Changes.** We may change our privacy policy at any time. Any changes to the privacy and security policy will be posted on this website so that you are aware of the information we collect, how we use it, as well as the latest updates on how we maintain the secure portions of our website.

**You’re Acceptance of These Terms.** By purchasing products or services from ControlPad or using the ControlPad website, you consent to the Privacy Policy for ControlPad. If you do not agree with this policy, please do not purchase from us and do not use our site. We reserve the right, at our discretion, to modify the ControlPad website following the posting of changes to these terms. Purchase of products or services or use of the ControlPad website indicates your consent to accept such changes.

**Contacting ControlPad.** Should you have any questions or comments regarding the products or services of ControlPad or this website, please feel free to contact us using the Contact Us tab on the website homepage or by emailing us at: info@controlpad.com'
            ]);
            CustomPage::create([
                'title' => 'Bank Account Authorization',
                'slug' => 'bank-account-authorization',
                'content' => 'I hereby authorize ' . config('site.company_name') . ' to make ACH direct deposits to my account listed above, including the listed institution, in order to authorize my bank account information. This permission is for two transactions, and any subsequent transactions that may be necessary to finalize my bank account authorization.

                Additionally, this agreement authorizes ' . config('site.company_name') . ' to make ACH direct deposits to my account in order to receive my earned funds or any other payments. This agreement does not authorize any other deposits or withdrawals.

                I agree that all direct deposit ACH transactions for money earned shall comply with US law. This agreement shall stay in effect until ' . config('site.company_name') . ' receives a written termination notice from myself and has a reasonable opportunity to act on it.'
            ]);
            CustomPage::create([
                'title' => 'Rep Terms',
                'slug' => 'rep-terms',
                'content' => ' '
            ]);
            DB::commit();
    }
}

<?php

namespace go1\util\queue;

/**
 * Note for developers who are publishing messages:
 *
 * - "Create" message must contain the full entity, not only ID.
 * - "Update" message must contain 'original' value.
 * - "Delete" message must contain the full entity, not only ID.
 */
class Queue
{
    public const DELETE_EVENTS = [
        self::PORTAL_DELETE,
        self::USER_DELETE,
        self::LO_DELETE,
        self::TAG_DELETE,
        self::ENROLMENT_DELETE,
    ];

    public const NOTIFY_TASKS = [
        self::NOTIFY_PORTAL_USER_PLAN,
    ];

    # recommendation
    public const RECOMMENDATION_POST_LEARNING_COMPLETION_SEND = 'recommendation.post-learning-completion.send';


    # The entity events
    # -------
    public const PORTAL_CREATE                     = 'portal.create';
    public const PORTAL_UPDATE                     = 'portal.update';
    public const PORTAL_DELETE                     = 'portal.delete';
    public const PORTAL_CONFIG_CREATE              = 'portal-config.create';
    public const PORTAL_CONFIG_UPDATE              = 'portal-config.update';
    public const PORTAL_CONFIG_DELETE              = 'portal-config.delete';

    /**
     * This event indicates a partner portal has requested its child portals to
     * inherit a subset of the partner portal's config based on groups in the event body
     *
     * Body:
     * {
     *   id: int|string # partner portal id or instance
     *   groups: string[] # list of config groups to be inherited by child portals
     * }
     * @see go1\util\portal\PartnerConfigurationsInheritance
     */
    public const PORTAL_CONFIG_PUBLISH_TO_CHILDREN  = 'portal-config.publish-to-children';

    /**
     * This event is published when a child portal needs to override a subset
     * of its config with its parent portal's config based on groups in the event body
     *
     * Body:
     * {
     *   id: int|string # child portal id or instance
     *   parent_portal_id: int|string # partner portal id or instance
     *   groups: string[] # list of config groups to be inherited by the child portal
     * }
     * @see go1\util\portal\PartnerConfigurationsInheritance
     */
    public const PORTAL_CONFIG_INHERIT_FROM_PARENT   = 'portal-config.inherit-from-parent';

    public const PORTAL_REQUEST_CREATE             = 'portal-request.create';
    public const CONTRACT_CREATE                   = 'contract.create';
    public const CONTRACT_UPDATE                   = 'contract.update';
    public const CONTRACT_DELETE                   = 'contract.delete';
    public const CONTRACT_VIEW_LIST                = 'contract.view.list';
    public const CONTRACT_VIEW_DETAIL              = 'contract.view.detail';
    public const CONTRACT_VIEW_SUBSCRIPTION        = 'contract.view.subscription';
    public const CONTRACT_CREATE_START             = 'contract.create.start';
    public const CONTRACT_CREATE_END               = 'contract.create.end';
    public const LO_CREATE                         = 'lo.create'; # Body: LO object, no lo.items should be expected.
    public const LO_UPDATE                         = 'lo.update'; # Body: LO object with extra property: origin.
    public const LO_DELETE                         = 'lo.delete'; # Body: LO object.
    public const LO_SHARE                          = 'lo.lo.share';
    public const LO_PLAYLIST_SHARE                 = 'lo.playlist.share';
    public const LO_SAVE_ASSESSORS                 = 'lo.save.assessors';          # Body: {body: [create: INT[], update: INT[], delete: INT[]], id: INT}
    public const USER_ACCOUNT_INVITE               = 'user.space-account.invite';
    public const USER_CREATE                       = 'user.create';
    public const USER_UPDATE                       = 'user.update';
    public const USER_DELETE                       = 'user.delete';
    public const USER_BULK_NOTIFY                  = 'user.bulk-notify';
    public const USER_FORGET_PASSWORD              = 'user.forget-password';
    public const USER_PASSWORD_CHANGE              = 'user.password.change';
    public const USER_PASSWORD_RESET               = 'user.password.reset';
    public const USER_LOGIN_SUCCESS                = 'user.login-success';
    public const USER_LOGIN_FAIL                   = 'user.login-fail';
    public const USER_LOGGED_IN                    = 'user.logged-in';
    public const USER_MASQUERADE                   = 'user.masquerade';
    public const USER_EMAIL_CREATE                 = 'user-email.create';
    public const USER_EMAIL_UPDATE                 = 'user-email.update';
    public const USER_EMAIL_DELETE                 = 'user-email.delete';
    public const RO_CREATE                         = 'ro.create';
    public const RO_UPDATE                         = 'ro.update';
    public const RO_DELETE                         = 'ro.delete';
    public const VOTE_CREATE                       = 'vote.create';
    public const VOTE_UPDATE                       = 'vote.update';
    public const VOTE_DELETE                       = 'vote.delete';
    public const CUSTOMER_CREATE                   = 'customer.create';
    public const CUSTOMER_UPDATE                   = 'customer.update';
    public const CUSTOMER_DELETE                   = 'customer.delete';
    public const CUSTOMER_VIEW_LIST                = 'customer.view.list';
    public const CUSTOMER_VIEW_DETAIL              = 'customer.view.detail';
    public const CUSTOMER_VIEW_PORTAL              = 'customer.view.portal';
    public const CUSTOMER_VIEW_CONTRACT            = 'customer.view.contract';
    public const CUSTOMER_VIEW_SUBSCRIPTION        = 'customer.view.subscription';
    public const CUSTOMER_CREATE_START             = 'customer.create.start';
    public const CUSTOMER_CREATE_END               = 'customer.create.end';
    public const PLAN_CREATE                       = 'plan.create';
    public const PLAN_UPDATE                       = 'plan.update';
    public const PLAN_DELETE                       = 'plan.delete';
    public const PLAN_REFERENCE_CREATE             = 'plan-reference.create';
    public const PLAN_REFERENCE_UPDATE             = 'plan-reference.update';
    public const PLAN_REFERENCE_DELETE             = 'plan-reference.delete';
    public const PLAN_REASSIGN                     = 'plan.re-assign'; # Body: {type: STRING,user_id: INT,assigner_id: INT,instance_id: INT,entity_type: STRING,entity_id: INT,status: INT,created_date: INT,due_date: INT}
    public const ENROLMENT_CREATE                  = 'enrolment.create';
    public const ENROLMENT_UPDATE                  = 'enrolment.update';
    public const ENROLMENT_DELETE                  = 'enrolment.delete';
    public const ENROLMENT_REVISION_CREATE         = 'enrolment-revision.create';
    public const ENROLMENT_SAVE_ASSESSORS          = 'enrolment.save.assessors';   # Body: {body: [create: INT[], update: INT[], delete: INT[]], id: INT}
    public const MANUAL_RECORD_CREATE              = 'manual-record.create';
    public const MANUAL_RECORD_UPDATE              = 'manual-record.update';
    public const MANUAL_RECORD_DELETE              = 'manual-record.delete';
    public const ONBOARD_COMPLETE                  = 'onboard.complete';
    public const TAG_CREATE                        = 'tag.create';
    public const TAG_UPDATE                        = 'tag.update';
    public const TAG_DELETE                        = 'tag.delete';
    public const CUSTOM_TAG_PUSH                   = 'custom-tag.push'; # Body: {instance_id: INT, lo_id: INT}
    public const CUSTOM_TAG_CREATE                 = 'custom-tag.create';
    public const CUSTOM_TAG_UPDATE                 = 'custom-tag.update';
    public const CUSTOM_TAG_DELETE                 = 'custom-tag.delete';
    public const COUPON_CREATE                     = 'coupon.create';
    public const COUPON_UPDATE                     = 'coupon.update';
    public const COUPON_DELETE                     = 'coupon.delete';
    public const COUPON_USE                        = 'coupon.use';
    public const TRANSACTION_CREATE                = 'transaction.create';
    public const TRANSACTION_UPDATE                = 'transaction.update';
    public const ASM_ASSIGNMENT_CREATE             = 'asm.assignment.create';
    public const ASM_ASSIGNMENT_UPDATE             = 'asm.assignment.update';
    public const ASM_ASSIGNMENT_DELETE             = 'asm.assignment.delete';
    public const ASM_SUBMISSION_CREATE             = 'asm.submission.create';
    public const ASM_SUBMISSION_UPDATE             = 'asm.submission.update';
    public const ASM_SUBMISSION_DELETE             = 'asm.submission.delete';
    public const ASM_FEEDBACK_CREATE               = 'asm.feedback.create';
    public const ASM_FEEDBACK_UPDATE               = 'asm.feedback.update';
    public const ASM_FEEDBACK_DELETE               = 'asm.feedback.delete';
    public const ALGOLIA_LO_UPDATE                 = 'algolia.lo.update'; # Lo Object {id: INT, type: STRING}
    public const ALGOLIA_LO_DELETE                 = 'algolia.lo.delete'; # Lo Object {id: INT, type: STRING}
    public const ECK_CREATE                        = 'eck.entity.create';
    public const ECK_UPDATE                        = 'eck.entity.update';
    public const ECK_DELETE                        = 'eck.entity.delete';
    public const ECK_METADATA_CREATE               = 'eck.metadata.create';
    public const ECK_METADATA_UPDATE               = 'eck.metadata.update';
    public const ECK_METADATA_DELETE               = 'eck.metadata.delete';
    public const FLAG_CREATE                       = 'flag.create';
    public const FLAG_UPDATE                       = 'flag.update';
    public const FLAG_DELETE                       = 'flag.delete';
    public const GROUP_CREATE                      = 'group.create';
    public const GROUP_UPDATE                      = 'group.update';
    public const GROUP_DELETE                      = 'group.delete';
    public const GROUP_ITEM_CREATE                 = 'group.item.create';
    public const GROUP_ITEM_UPDATE                 = 'group.item.update';
    public const GROUP_ITEM_DELETE                 = 'group.item.delete';
    public const GROUP_ASSIGN_CREATE               = 'group.assign.create';
    public const GROUP_ASSIGN_UPDATE               = 'group.assign.update';
    public const GROUP_ASSIGN_DELETE               = 'group.assign.delete';
    public const HISTORY_RECORD                    = 'history.record';
    public const NOTE_CREATE                       = 'note.create';
    public const NOTE_UPDATE                       = 'note.update';
    public const NOTE_DELETE                       = 'note.delete';
    public const NOTIFY_CONFIG_CREATE              = 'notify_config.create';
    public const NOTIFY_CONFIG_SAVE                = 'notify_config.save';
    public const REPORT_CREATE                     = 'report.create';
    public const REPORT_UPDATE                     = 'report.update';
    public const REPORT_DELETE                     = 'report.delete';
    public const AWARD_CREATE                      = 'award.create';
    public const AWARD_UPDATE                      = 'award.update';
    public const AWARD_DELETE                      = 'award.delete';
    public const AWARD_ITEM_CREATE                 = 'award.item.create';
    public const AWARD_ITEM_UPDATE                 = 'award.item.update';
    public const AWARD_ITEM_DELETE                 = 'award.item.delete';
    public const AWARD_ITEM_MANUAL_CREATE          = 'award.item-manual.create';
    public const AWARD_ITEM_MANUAL_UPDATE          = 'award.item-manual.update';
    public const AWARD_ITEM_MANUAL_DELETE          = 'award.item-manual.delete';
    public const AWARD_ACHIEVEMENT_CREATE          = 'award.achievement.create';
    public const AWARD_ACHIEVEMENT_UPDATE          = 'award.achievement.update';
    public const AWARD_ACHIEVEMENT_DELETE          = 'award.achievement.delete';
    public const AWARD_ENROLMENT_CREATE            = 'award.enrolment.create';
    public const AWARD_ENROLMENT_UPDATE            = 'award.enrolment.update';
    public const AWARD_ENROLMENT_DELETE            = 'award.enrolment.delete';
    public const AWARD_ITEM_ENROLMENT_CREATE       = 'award.item.enrolment.create';
    public const AWARD_ITEM_ENROLMENT_UPDATE       = 'award.item.enrolment.update';
    public const AWARD_ITEM_ENROLMENT_DELETE       = 'award.item.enrolment.delete';
    public const MAIL_LOG_CREATE                   = 'mail-log.create';
    public const NOTIFY_PORTAL_USER_PLAN           = 'notify.portal.user_plan_reached';
    public const QUIZ_USER_ANSWER_CREATE           = 'quiz.user_answer.create';
    public const QUIZ_USER_ANSWER_UPDATE           = 'quiz.user_answer.update';
    public const QUIZ_USER_ANSWER_DELETE           = 'quiz.user_answer.delete';
    public const QUIZ_QUESTION_RESULT_DELETE       = 'quiz.question_result.delete';
    public const LOCATION_CREATE                   = 'location.create';
    public const LOCATION_UPDATE                   = 'location.update';
    public const LOCATION_DELETE                   = 'location.delete';
    public const LO_GROUP_CREATE                   = 'lo_group.create';
    public const LO_GROUP_DELETE                   = 'lo_group.delete';
    public const CREDIT_CREATE                     = 'credit.create';
    public const CREDIT_UPDATE                     = 'credit.update';
    public const CREDIT_DELETE                     = 'credit.delete';
    public const CREDIT_USAGE_CREATE               = 'credit_usage.create';
    public const ROLE_CREATE                       = 'role.create';
    public const ROLE_UPDATE                       = 'role.update';
    public const ROLE_DELETE                       = 'role.delete';
    public const ACTIVITY_CREATE                   = 'activity.create';
    public const ACTIVITY_UPDATE                   = 'activity.update';
    public const ACTIVITY_DELETE                   = 'activity.delete';
    public const METRIC_CREATE                     = 'metric.create';
    public const METRIC_UPDATE                     = 'metric.update';
    public const METRIC_DELETE                     = 'metric.delete';
    public const PAYMENT_STRIPE_AUTHORIZE          = 'payment.stripe.authorize';
    public const PAYMENT_STRIPE_DEAUTHORIZE        = 'payment.stripe.deauthorize';
    public const NOTE_COMMENT_CREATE               = 'note_comment.create';
    public const NOTE_COMMENT_UPDATE               = 'note_comment.update';
    public const NOTE_COMMENT_DELETE               = 'note_comment.delete';
    public const GROUP_COLLECTION_CREATE           = 'group_collection.create';
    public const GROUP_COLLECTION_UPDATE           = 'group_collection.update';
    public const GROUP_COLLECTION_DELETE           = 'group_collection.delete';
    public const GROUP_COLLECTION_ITEM_CREATE      = 'group_collection_item.create';
    public const GROUP_COLLECTION_ITEM_UPDATE      = 'group_collection_item.update';
    public const GROUP_COLLECTION_ITEM_DELETE      = 'group_collection_item.delete';
    public const COLLECTION_GROUP_SELECTION_CREATE = 'collection_group_selection.create';
    public const COLLECTION_GROUP_SELECTION_UPDATE = 'collection_group_selection.update';
    public const COLLECTION_GROUP_SELECTION_DELETE = 'collection_group_selection.delete';
    public const POLICY_ITEM_CREATE                = 'policy.item.create';
    public const POLICY_ITEM_UPDATE                = 'policy.item.update';
    public const POLICY_ITEM_DELETE                = 'policy.item.delete';
    public const POLICY_ITEM_SYNC                  = 'policy.item.sync';
    public const PAGEUP_COURSE_UPLOAD              = 'pageup.course.upload';
    public const EXIM_TASK_UPDATE                  = 'exim.task.update';
    public const PURCHASE_REQUEST_CREATE           = 'purchase.request.create';
    public const PURCHASE_REQUEST_UPDATE           = 'purchase.request.update';
    public const PURCHASE_REQUEST_DELETE           = 'purchase.request.delete';
    public const EVENT_SESSION_CREATE              = 'event.session.create';
    public const EVENT_SESSION_UPDATE              = 'event.session.update';
    public const EVENT_SESSION_DELETE              = 'event.session.delete';
    public const EVENT_LOCATION_CREATE             = 'event.location.create';
    public const EVENT_LOCATION_UPDATE             = 'event.location.update';
    public const EVENT_LOCATION_DELETE             = 'event.location.delete';
    public const LI_VIDEO_PROCESS_S3               = 'li_video.process.s3';
    public const LI_AUDIO_PROCESS_S3               = 'li_audio.process.s3';
    public const LO_UPDATE_ATTRIBUTES              = 'lo.update.attributes';
    public const CONTENT_IMPORT_PROCESS_IMPORT     = 'content_import.process.import';
    public const CONTENT_IMPORT_PROCESS_IMPORT_JOB = 'content_import_job.process.import';
    public const MARKETPLACE_SCHEDULED_PUBLISH     = 'marketplace.scheduled_publish';
    public const MARKETPLACE_SYNC_CHILD            = 'marketplace.sync_child';
    public const MERGE_ACCOUNT_ENROLMENT_REVISION  = 'merge-account.enrolment-revision'; // Change profile_id of enrolment revisions, body: {profile_id, portal_id}
    public const CONTENT_JOB_PROCESS_IMPORT        = 'content_job.process.import';
    public const CONTENT_JOB_PROCESS_IMPORT_JOB    = 'content_job_job.process.import';
    public const ACCESS_SESSIONS_INVALIDATED       = 'access.sessions.invalidated';

    # user-domain events.
    public const USER_DOMAIN_USER_CREATE           = 'user-domain.user.create';
    public const USER_DOMAIN_USER_UPDATE           = 'user-domain.user.update';
    public const USER_DOMAIN_PORTAL_ACCOUNT_CREATE = 'user-domain.portal-account.create';
    public const USER_DOMAIN_PORTAL_ACCOUNT_UPDATE = 'user-domain.portal-account.update';
    public const USER_DOMAIN_PORTAL_ACCOUNT_DELETE = 'user-domain.portal-account.delete';

    # account-fields events
    public const ACCOUNT_FIELDS_CREATE             = 'account-fields.create';
    public const ACCOUNT_FIELDS_UPDATE             = 'account-fields.update';
    public const ACCOUNT_FIELDS_DELETE             = 'account-fields.delete';

    /**
     * @deprecated
     *
     * Let #work's consumer handle your message.
     *
     * This routingKey should be not used directly, it's being used internal by MqClient::queue()
     *
     * - if exchange is not provided (empty string)
     *      - routingKey then will be set to this constant.
     *      - the body is also altered to { routingKey: STRING, body: ORIGIN_BODY }
     *
     * When you use #work, it's depends on your use case:
     *
     *  - #consumer processes messages synchronously
     *  - #work     processes messages asynchronously
     */
    public const WORKER_QUEUE_NAME = 'worker';

    # routingKey that tell some service to do something.
    #
    # Note
    # =======
    # We should not add a lot of routing keys for each task. Each should define only one DO routing key for each service.
    # For example:
    #   - Should not define:
    #       - DO_ENROLMENT_CHECK_MODULE_ENROLMENTS = 'do.etc.xxxxx' # { BODY }
    #       - DO_ENROLMENT_CHECK_MODULE_ENROLMENT  = 'do.etc.xxxxx' # { BODY }
    #   - Should:
    #       - DO_ENROLMENT = 'do.etc' # { task: TASK_NAME, body: TASK_BODY }
    #
    # The #consumer auto routing the message to #SERVICE when the routing key is "do.SERVICE".
    # -------
    public const DO_CONSUMER_HTTP_REQUEST             = 'do.consumer.HttpRequest'; # { method: STRING, url: STRING, query: STRING, headers: map[STRING][STRING], body: STRING }
    public const DO_FINDER                            = 'do.finder';
    public const DO_PUBLIC_API_WEBHOOK_REQUEST        = 'do.public-api.webhook-request'; # { appId: INT, url: STRING, subject: OBJECT, original: null|OBJECT }
    public const DO_MAIL_SEND                         = 'do.mail.send'; # { subject: STRING, body: STRING, html: STRING, context: OBJECT, attachments: STRING[], options: OBJECT }
    public const DO_MAIL_BULK_SEND                    = 'mail-bulk.send'; # { subject: STRING, body: STRING, html: STRING, context: OBJECT, attachments: STRING[], options: OBJECT }
    public const DO_HISTORY_RECORD                    = 'do.history.record';
    public const DO_ENROLMENT                         = 'process.enrolment'; # { action: STRING, body: OBJECT }
    public const DO_ENROLMENT_CRON                    = 'etc.do.cron'; # { task: STRING }
    public const DO_ENROLMENT_CHECK_MODULE_ENROLMENTS = 'etc.do.check-module-enrolments'; # { moduleId: INT }
    public const DO_ENROLMENT_CHECK_MODULE_ENROLMENT  = 'etc.do.check-module-enrolment'; # { moduleId: INT, enrolmentId: INT }
    public const DO_ENROLMENT_CREATE                  = 'etc.do.create'; # { … }
    public const DO_ENROLMENT_UPDATE                  = 'etc.do.update'; # { KEY_N: MIXED|NULL }
    public const DO_ENROLMENT_DELETE                  = 'etc.do.delete'; # { KEY_N: MIXED|NULL }
    public const DO_ENROLMENT_PLAN_CREATE             = 'etc.do.plan.create'; # Plan Object
    public const DO_EXIM_IMPORT_ENROLLMENT            = 'do.exim.import-enrolment'; # {user_id, lo_id, instance_id, notify, manager_id}
    public const DO_EXIM_IMPORT_AWARD_ENROLLMENT      = 'do.exim.import-award-enrolment'; # {award_id, instance_id, user_ids}
    public const DO_EXIM_IMPORT_USER                  = 'do.exim.import-user'; # {$instance, $mail, $first, $last, $status, $manager}
    public const DO_EXIM_IMPORT                       = 'do.exim.import'; # { data: OBJECT[], taskId: INT }
    public const DO_SMS_SEND                          = 'do.sms.send'; # { to: STRING, body: STRING }
    public const DO_GRAPHIN_IMPORT                    = 'do.graphin.import'; # { type: STRING, id: INT }
    // @deprecated by no longer use virtual account
    public const DO_USER_CREATE_VIRTUAL_ACCOUNT = 'do.user.virtual-account'; # { type: STRING, object: enrolment/??? object}
    public const DO_USER_DELETE                 = 'do.user.delete'; # User Object
    public const DO_USER_IMPORT                 = 'do.user.import'; # {$instance, $mail, $first, $last, $status, $manager}
    public const DO_ALGOLIA_INDEX               = 'do.algolia.index'; # Object { offset: INT, limit: INT}
    public const DO_USER_UNBLOCK_MAIL           = 'do.user.unblock.mail'; # String mail
    public const DO_USER_UNBLOCK_IP             = 'do.user.unblock.ip'; # String ip
    public const DO_NOTIFY                      = 'do.notify'; # {task: string NOTIFY_TASKS, body: array TASK_BODY}
    public const DO_AWARD_ITEM                  = 'do.award.item'; # { task: STRING, body: TASK_BODY }
    public const DO_AWARD_CRON                  = 'do.award.cron'; # { task: STRING }
    public const DO_AWARD_CALCULATE             = 'do.award.calculate'; # {task: AWARD_TASK, body: array TASK_BODY}
    public const DO_AWARD_PLAN_CREATE           = 'do.award.plan.create'; # Plan Object
    public const DO_INDEX                       = 'do.index'; # {index: STRING, type: string, operation: enum(index,create,update,delete,bulk), body: OBJECT, routing: STRING, parent: STRING}
    public const DO_MYTEAM                      = 'process.my-team'; # { action: STRING, body: OBJECT }
    public const DO_ASSESSOR                    = 'do.assessor'; # { task: string, body: OBJECT }
    public const DO_PAGEUP_UPLOAD_COURSE        = 'do.pageup.upload-couse'; # { $portal_id, $course_id }
    public const REINDEX_PREFIX                 = 'go1-reindex.';

    /**
     * TEMPORARY EVENT (will be removed when premium/region restriction propagation is removed
     *
     * This event is to enable the marketplace consumer to break up the updating of the learning objects
     * into manageable groups (of 5000 LOs)
     *
     * body = {
     *      group: OBJECT, // the group object in which the Learning objects are.
     *      offset: the starting offset in the list of learning objects
     *      limit: the number of learning objects to process
     * }
     */
    public const MARKETPLACE_UPDATE_LO_REGIONS = 'update.lo.regions';

    public static function postEvent(string $event): string
    {
        return "post_{$event}";
    }
}

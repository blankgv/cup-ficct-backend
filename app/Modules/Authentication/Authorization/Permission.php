<?php

namespace App\Modules\Authentication\Authorization;

// Catálogo de permisos del sistema.
final class Permission
{
    // Authentication
    public const USER_MANAGE = 'user.manage';
    public const ROLE_MANAGE = 'role.manage';

    // Gestión académica
    public const ACADEMIC_MANAGE = 'academic.manage';

    // Admisión
    public const APPLICANT_MANAGE = 'applicant.manage';
    public const APPLICANT_VERIFY = 'applicant.verify';
    public const APPLICANT_ASSIGN = 'applicant.assign';

    // Pagos
    public const PAYMENT_MANAGE = 'payment.manage';

    // Evaluación
    public const GRADE_MANAGE = 'grade.manage';
    public const ATTENDANCE_MANAGE = 'attendance.manage';

    // Reportes
    public const REPORT_VIEW = 'report.view';
    public const REPORT_EXPORT = 'report.export';

    /**
     * Todos los permisos.
     *
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::USER_MANAGE,
            self::ROLE_MANAGE,
            self::ACADEMIC_MANAGE,
            self::APPLICANT_MANAGE,
            self::APPLICANT_VERIFY,
            self::APPLICANT_ASSIGN,
            self::PAYMENT_MANAGE,
            self::GRADE_MANAGE,
            self::ATTENDANCE_MANAGE,
            self::REPORT_VIEW,
            self::REPORT_EXPORT,
        ];
    }
}

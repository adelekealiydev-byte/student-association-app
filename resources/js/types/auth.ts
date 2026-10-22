export type MembershipStatus = 'Pending Invite' | 'Active' | 'Suspended' | 'Alumni' | 'Expired';

export type Member = {
    member_id: number;
    email: string;
    student_number: string | null;
    username: string | null;
    membership_status: MembershipStatus;
    is_verified: boolean;
    roles: string[];
    [key: string]: unknown;
};

export type User = Member & {
    id?: number;
    name?: string;
    avatar?: string;
    email_verified_at?: string | null;
};

export type Auth = {
    user: Member | null;
};

/* @chisel-passkeys */
export type Passkey = {
    id: number;
    name: string;
    authenticator: string | null;
    created_at_diff: string;
    last_used_at_diff: string | null;
};
/* @end-chisel-passkeys */

/* @chisel-2fa */
export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};
/* @end-chisel-2fa */

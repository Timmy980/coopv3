export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface User {
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    phone_number?: string;
}

export interface AccountType {
    id: number;
    name: string;
    description?: string;
    interest_rate: number;
}

export interface MemberAccount {
    id: number;
    account_number: string;
    user?: User;
    account_type: AccountType;
    balance: number;
    status: 'active' | 'inactive' | 'suspended';
    savings_count: number;
    withdrawal_requests_count: number;
}

export interface CooperativeAccount {
    id: number;
    account_name: string;
    account_number: string;
}

export interface Saving {
    id: number;
    member_account: MemberAccount;
    cooperative_account: CooperativeAccount;
    amount: number;
    transaction_date: string;
    reference_number: string;
    source: 'member_gateway' | 'member_deposit' | 'admin_single' | 'admin_bulk';
    status: 'pending' | 'approved' | 'rejected' | 'failed';
    payment_proof?: string;
    notes?: string;
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    from: number;
    to: number;
    total: number;
    per_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
    links: PaginationLink[];
}

export interface SavingsFilterData {
    status: string;
    source: string;
    date_from: string;
    date_to: string;
} 
<div
    id="reminder-toast-stack"
    class="pointer-events-none fixed inset-x-0 top-[calc(0.75rem+env(safe-area-inset-top))] z-[60] mx-auto w-full max-w-lg px-4"
    data-reminders-url="{{ route('reminders.index') }}"
    data-reminders='@json($expiringReminders ?? [], JSON_UNESCAPED_UNICODE)'
    data-oil-url="{{ route('oil.index') }}"
    data-oil-messages='@json($oilChangeToasts ?? [], JSON_UNESCAPED_UNICODE)'
    data-oil-dismiss-url="{{ route('oil-change-toasts.dismiss') }}"
    hidden
></div>

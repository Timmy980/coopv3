export const useSavingsFormatters = () => {
    const formatDate = (date: string) => {
      return new Date(date).toLocaleDateString('en-NG', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    };
  
    const formatDateWithTime = (date: string) => {
      return new Date(date).toLocaleDateString('en-NG', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    };
  
    const formatAmount = (amount: number) => {
      return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN'
      }).format(amount || 0);
    };
  
    const formatSource = (source: string) => {
      const sources = {
        member_gateway: 'Gateway Payment',
        member_deposit: 'Bank Deposit',
        admin_single: 'Admin Entry',
        admin_bulk: 'Admin Upload'
      };
      return sources[source as keyof typeof sources] || source;
    };
  
    const getSourceClass = (source: string) => {
      switch (source) {
        case 'member_gateway':
          return 'px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800';
        case 'member_deposit':
          return 'px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800';
        case 'admin_single':
          return 'px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800';
        case 'admin_bulk':
          return 'px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-pink-100 text-pink-800';
        default:
          return 'px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800';
      }
    };
  
    const getStatusClass = (status: string) => {
      switch (status) {
        case 'pending':
          return 'px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800';
        case 'approved':
          return 'px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800';
        case 'rejected':
          return 'px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800';
        case 'failed':
          return 'px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800';
        default:
          return 'px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800';
      }
    };
  
    return {
      formatDate,
      formatDateWithTime,
      formatAmount,
      formatSource,
      getSourceClass,
      getStatusClass
    };
  };
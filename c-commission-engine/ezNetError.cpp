#include "ezNetError.h"

#include <errno.h>

////////////////////////////
// Lookup a pselect error //
////////////////////////////
string CezNetError::GetErrorInfo(int errornum)
{
	switch (errornum)
	{
	case E2BIG:
		return "E2BIG: Argument list too long (POSIX.1)";
	case EACCES:
		return "EACCES: Permission denied (POSIX.1)";
	case EADDRINUSE:
		return "EADDRINUSE: Address already in use (POSIX.1)";
	case EADDRNOTAVAIL:
		return "EADDRNOTAVAIL: Address not available (POSIX.1)";
	case EAFNOSUPPORT:
		return "EAFNOSUPPORT: Address family not supported (POSIX.1)";
	case EAGAIN:
		return "EAGAIN: Resource temporarily unavailable (may be the same value as EWOULDBLOCK) (POSIX.1)";
	case EALREADY:
		return "EALREADY: Connection already in progress (POSIX.1)";
	case EBADE:
		return "EBADE: Invalid exchange";
	case EBADF:
		return "EBADF: Bad file descriptor (POSIX.1)";
	case EBADFD:
		return "EBADF: File descriptor in bad state";
	case EBADMSG:
		return "EBADMSG: Bad message (POSIX.1)";
	case EBADR:
		return "EBADR: Invalid request descriptor";
	case EBADRQC:
		return "EBADRQC: Invalid request code";
	case EBADSLT:
		return "EBADSLT: Invalid slot";
	case EBUSY:
		return "EBUSY: Device or resource busy (POSIX.1)";
	case ECANCELED:
		return "ECANCELED: Operation canceled (POSIX.1)";
	case ECHILD:
		return "ECHILD: No child processes (POSIX.1)";
	case ECHRNG:
		return "ECHRNG: Channel number out of range";
	case ECOMM:
		return "ECOMM: Communication error on send";
	case ECONNABORTED:
		return "ECONNABORTED: Connection aborted (POSIX.1)";
	case ECONNREFUSED:
		return "ECONNREFUSED: Connection refused (POSIX.1)";
	case ECONNRESET:
		return "ECONNREFUSED: Connection reset (POSIX.1)";
	//case EDEADLK:
	//	return "EDEADLK: Resource deadlock avoided (POSIX.1)";
	case EDESTADDRREQ:
		return "EDESTADDRREQ: Destination address required (POSIX.1)";
	case EDOM:
		return "EDOM: Mathematics argument out of domain of function (POSIX.1, C99)";
	case EDQUOT:
		return "EDQUOT: Disk quota exceeded (POSIX.1)";
	case EEXIST:
		return "EEXIST: File exists (POSIX.1)";
	case EFAULT:
		return "EFAULT: Bad address (POSIX.1)";
	case EFBIG:
		return "EFBIG: File too large (POSIX.1)";
	case EHOSTDOWN:
		return "EHOSTDOWN: Host is down";
	case EHOSTUNREACH:
		return "EHOSTUNREACH: Host is unreachable (POSIX.1)";
	case EIDRM:
		return "EIDRM: Identifier removed (POSIX.1)";
	case EILSEQ:
		return "EILSEQ: Illegal byte sequence (POSIX.1, C99)";
	case EINPROGRESS:
		return "EINPROGRESS: Operation in progress (POSIX.1)";
	case EINTR:
		return "EINTR: Interrupted function call (POSIX.1); see signal(7)";
	case EINVAL:
		return "EINVAL: Invalid argument (POSIX.1)";
	case EIO:
		return "EIO: Input/output error (POSIX.1)";
	case EISCONN:
		return "EISCONN: Socket is connected (POSIX.1)";
	case EISDIR:
		return "EISDIR: Is a directory (POSIX.1)";
	case EISNAM:
		return "EISNAM: Is a named type file";
	case EKEYEXPIRED:
		return "EKEYEXPIRED: Key has expired";
	case EKEYREJECTED:
		return "EKEYREJECTED: Key was rejected by service";
	case EKEYREVOKED:
		return "EKEYREVOKED: Key has been revoked";
	case EL2HLT:
		return "EL2HLT: Level 2 halted";
	case EL2NSYNC:
		return "EL2NSYNC: Level 2 not synchronized";
	case EL3HLT:
		return "EL3HLT: Level 3 halted";
	case EL3RST:
		return "EL3RST: Level 3 halted";
	case ELIBACC:
		return "ELIBACC: Cannot access a needed shared library";
	case ELIBBAD:
		return "ELIBBAD: Accessing a corrupted shared library";
	case ELIBMAX:
		return "ELIBMAX: Attempting to link in too many shared libraries";
	case ELIBSCN:
		return "ELIBSCN: lib section in a.out corrupted";
	case ELIBEXEC:
		return "ELIBEXEC: Cannot exec a shared library directly";
	case ELOOP:
		return "ELOOP: Too many levels of symbolic links (POSIX.1)";
	case EMEDIUMTYPE:
		return "EMEDIUMTYPE: Wrong medium type";
	case EMFILE:
		return "EMFILE: Too many open files (POSIX.1)";
	case EMLINK:
		return "EMLINK: Too many links (POSIX.1)";
	case EMSGSIZE:
		return "EMSGSIZE: Message too long (POSIX.1)";
	case EMULTIHOP:
		return "EMULTIHOP: Multihop attempted (POSIX.1)";
	case ENAMETOOLONG:
		return "ENAMETOOLONG: Filename too long (POSIX.1)";
	case ENETDOWN:
		return "ENETDOWN: Network is down (POSIX.1)";
	case ENETRESET:
		return "ENETRESET: Network is down (POSIX.1)";
	case ENETUNREACH:
		return "ENETUNREACH: Network unreachable (POSIX.1)";
	case ENFILE:
		return "ENFILE: Too many open files in system (POSIX.1)";
	case ENOBUFS:
		return "ENOBUFS: No buffer space available (POSIX.1 (XSI STREAMS option))";
	case ENODATA:
		return "ENODATA: No message is available on the STREAM head read queue (POSIX.1)";
	case ENODEV:
		return "ENODEV: No such device (POSIX.1)";
	case ENOENT:
		return "ENOENT: No such file or directory (POSIX.1)";
	case ENOEXEC:
		return "ENOEXEC: Exec format error (POSIX.1)";
	case ENOKEY:
		return "ENOKEY: Required key not available";
	case ENOLCK:
		return "ENOLCK: No locks available (POSIX.1)";
	case ENOLINK:
		return "ENOLINK: Link has been severed (POSIX.1)";
	case ENOMEDIUM:
		return "ENOMEDIUM: No medium found";
	case ENOMEM:
		return "ENOMEM: Not enough space (POSIX.1)";
	case ENOMSG:
		return "ENOMSG: No message of the desired type (POSIX.1)";
	case ENONET:
		return "ENONET: Machine is not on the network";
	case ENOPKG:
		return "ENOPKG: Package not installed";
	case ENOPROTOOPT:
		return "ENOPROTOOPT: Protocol not available (POSIX.1)";
	case ENOSPC:
		return "ENOSPC: No space left on device (POSIX.1)";
	case ENOSR:
		return "ENOSR: No STREAM resources (POSIX.1 (XSI STREAMS option))";
	case ENOSTR:
		return "ENOSTR: Not a STREAM (POSIX.1 (XSI STREAMS option))";
	case ENOSYS:
		return "ENOSYS: Function not implemented (POSIX.1)";
	case ENOTBLK:
		return "ENOTBLK: Block device required";
	case ENOTCONN:
		return "ENOTCONN: The socket is not connected (POSIX.1)";
	case ENOTDIR:
		return "ENOTDIR: Not a directory (POSIX.1)";
	case ENOTEMPTY:
		return "ENOTEMPTY: Directory not empty (POSIX.1)";
	case ENOTSOCK:
		return "ENOTSOCK: Not a socket (POSIX.1)";
	//case ENOTSUP:
	//	return "ENOTSUP: Operation not supported (POSIX.1)";
	case ENOTTY:
		return "ENOTTY: Inappropriate I/O control operation (POSIX.1)";
	case ENOTUNIQ:
		return "ENOTUNIQ: Name not unique on network";
	case ENXIO:
		return "ENXIO: No such device or address (POSIX.1)";
	case EOPNOTSUPP:
		return "EOPNOTSUPP: Operation not supported on socket (POSIX.1)";
	case EOVERFLOW:
		return "EOVERFLOW: Value too large to be stored in data type (POSIX.1)";
	case EPERM:
		return "EPERM: Operation not permitted (POSIX.1)";
	case EPFNOSUPPORT:
		return "EPFNOSUPPORT: Protocol family not supported";
	case EPIPE:
		return "EPIPE: Broken pipe (POSIX.1)";
	case EPROTO:
		return "EPROTO: Protocol error (POSIX.1)";
	case EPROTONOSUPPORT:
		return "EPROTONOSUPPORT: Protocol not supported (POSIX.1)";
	case EPROTOTYPE:
		return "EPROTOTYPE: Protocol wrong type for socket (POSIX.1)";
	case ERANGE:
		return "ERANGE: Result too large (POSIX.1, C99)";
	case EREMCHG:
		return "EREMCHG: Remote address changed";
	case EREMOTE:
		return "EREMOTE: Object is remote";
	case EREMOTEIO:
		return "EREMOTEIO: Remote I/O error";
	case ERESTART:
		return "ERESTART: Interrupted system call should be restarted";
	case EROFS:
		return "EROFS: Read-only file system (POSIX.1)";
	case ESHUTDOWN:
		return "ESHUTDOWN: Cannot send after transport endpoint shutdown";
	case ESPIPE:
		return "ESPIPE: Invalid seek (POSIX.1)";
	case ESOCKTNOSUPPORT:
		return "ESOCKTNOSUPPORT: Socket type not supported";
	case ESRCH:
		return "ESRCH: No such process (POSIX.1)";
	case ESTALE:
		return "ESTALE: Stale file handle (POSIX.1)";
	case ESTRPIPE: // This error can occur for NFS and for other file systems
		return "ESTRPIPE: Streams pipe error";
	case ETIME: 
		return "ETIME: Timer expired (POSIX.1 (XSI STREAMS option))";
	case ETIMEDOUT: // (POSIX.1 says "STREAM ioctl(2) timeout")
		return "ETIMEDOUT: Connection timed out (POSIX.1)";
	case ETXTBSY: 
		return "ETXTBSY: Text file busy (POSIX.1)";
	case EUCLEAN: 
		return "EUCLEAN: Structure needs cleaning";
	case EUNATCH: 
		return "EUNATCH: Protocol driver not attached";
	case EUSERS: 
		return "EUSERS: Too many users";
	//case EWOULDBLOCK: 
	//	return "EWOULDBLOCK: Operation would block (may be same value as EAGAIN) (POSIX.1)";
	case EXDEV: 
		return "EXDEV: Improper link (POSIX.1)";
	case EXFULL: 
		return "EXFULL: Exchange full";
	default:
		return "(default) Error not found";
	}
}

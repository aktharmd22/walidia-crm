import { Head, Link } from '@inertiajs/react'
import { Button } from '@/ui/Button'

const messages: Record<number, { title: string; body: string }> = {
  403: {
    title: 'You do not have access to this',
    body: 'Your role does not include this screen. If you need it, an Admin can grant the permission.',
  },
  404: {
    title: 'Not found',
    body: 'This record does not exist, or it is not one you have access to.',
  },
  419: {
    title: 'Your session expired',
    body: 'Sessions end after 8 hours of inactivity. Sign in again and your work is where you left it.',
  },
  429: {
    title: 'Too many requests',
    body: 'Give it a moment and try again.',
  },
  500: {
    title: 'Something went wrong',
    body: 'The error has been logged. Nothing you entered was lost.',
  },
  503: {
    title: 'Down for maintenance',
    body: 'The platform is being updated. This is usually a few minutes.',
  },
}

export default function Status({ status }: { status: number }) {
  const message = messages[status] ?? messages[500]

  return (
    <div className="flex min-h-screen items-center justify-center bg-deck px-4">
      <Head title={String(status)} />
      <div className="w-full max-w-[440px] rounded-card border border-line bg-hull p-6 text-center">
        <p className="numeric text-display text-ink-faint">{status}</p>
        <h1 className="mt-2 text-h1 text-ink">{message.title}</h1>
        <p className="mt-2 text-body text-ink-soft">{message.body}</p>
        <Link href="/" className="mt-6 inline-block">
          <Button variant="primary">Back to My Day</Button>
        </Link>
      </div>
    </div>
  )
}

Status.layout = undefined

import { Head, useForm } from '@inertiajs/react'
import type { FormEvent } from 'react'
import { Button } from '@/ui/Button'
import { Input } from '@/ui/Field'

export default function ForgotPassword({ status }: { status?: string }) {
  const { data, setData, post, processing, errors } = useForm({ email: '' })

  function submit(event: FormEvent) {
    event.preventDefault()
    post('/forgot-password')
  }

  return (
    <div className="flex min-h-screen items-center justify-center bg-deck px-4 py-12">
      <Head title="Reset password" />

      <div className="w-full max-w-[400px]">
        <img
          src="/images/walidia-logo.png"
          alt="Walidia Yachts"
          className="mx-auto mb-8 h-[44px] w-auto object-contain"
        />

        <div className="rounded-card border border-line bg-hull p-6">
          <h1 className="text-h1 text-ink">Reset your password</h1>
          <p className="mt-2 text-body text-ink-soft">
            We will email a reset link. It expires in 60 minutes and can be used once.
          </p>

          {status && (
            <p className="mt-4 rounded-card border border-success bg-success-bg px-3 py-2 text-small text-success">
              {status}
            </p>
          )}

          <form onSubmit={submit} className="mt-6 flex flex-col gap-3">
            <Input
              label="Email"
              type="email"
              required
              autoFocus
              value={data.email}
              error={errors.email}
              onChange={(event) => setData('email', event.target.value)}
            />
            <Button type="submit" variant="primary" size="lg" block loading={processing} className="mt-3">
              Email reset link
            </Button>
          </form>

          <a href="/login" className="mt-4 inline-block text-small text-accent hover:underline">
            Back to sign in
          </a>
        </div>
      </div>
    </div>
  )
}

ForgotPassword.layout = undefined

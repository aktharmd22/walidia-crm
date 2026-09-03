import { Head, useForm } from '@inertiajs/react'
import type { FormEvent } from 'react'
import { Button } from '@/ui/Button'
import { PasswordInput } from '@/ui/Field'

export default function ConfirmPassword() {
  const { data, setData, post, processing, errors } = useForm({ password: '' })

  function submit(event: FormEvent) {
    event.preventDefault()
    post('/user/confirm-password')
  }

  return (
    <div className="flex min-h-screen items-center justify-center bg-deck px-4 py-12">
      <Head title="Confirm password" />

      <div className="w-full max-w-[400px]">
        <img
          src="/images/walidia-logo.png"
          alt="Walidia Yachts"
          className="mx-auto mb-8 h-[44px] w-auto object-contain"
        />

        <div className="rounded-card border border-line bg-hull p-6">
          <h1 className="text-h1 text-ink">Confirm your password</h1>
          <p className="mt-2 text-body text-ink-soft">
            This area changes security settings, so we ask for your password again.
          </p>

          <form onSubmit={submit} className="mt-6 flex flex-col gap-3">
            <PasswordInput
              label="Password"
              autoComplete="current-password"
              required
              autoFocus
              value={data.password}
              error={errors.password}
              onChange={(event) => setData('password', event.target.value)}
            />
            <Button type="submit" variant="primary" size="lg" block loading={processing} className="mt-3">
              Confirm
            </Button>
          </form>
        </div>
      </div>
    </div>
  )
}

ConfirmPassword.layout = undefined

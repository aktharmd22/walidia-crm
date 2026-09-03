import { useState, type FormEvent } from 'react'
import { Head, useForm } from '@inertiajs/react'
import { Button } from '@/ui/Button'
import { Input } from '@/ui/Field'

export default function TwoFactorChallenge() {
  const [useRecovery, setUseRecovery] = useState(false)
  const { data, setData, post, processing, errors } = useForm({ code: '', recovery_code: '' })

  function submit(event: FormEvent) {
    event.preventDefault()
    post('/two-factor-challenge')
  }

  return (
    <div className="flex min-h-screen items-center justify-center bg-deck px-4 py-12">
      <Head title="Two-factor authentication" />

      <div className="w-full max-w-[400px]">
        <img
          src="/images/walidia-logo.png"
          alt="Walidia Yachts"
          className="mx-auto mb-8 h-[44px] w-auto object-contain"
        />

        <div className="rounded-card border border-line bg-hull p-6">
          <h1 className="text-h1 text-ink">Two-factor authentication</h1>
          <p className="mt-2 text-body text-ink-soft">
            {useRecovery
              ? 'Enter one of your recovery codes.'
              : 'Enter the six-digit code from your authenticator app.'}
          </p>

          <form onSubmit={submit} className="mt-6 flex flex-col gap-3">
            {useRecovery ? (
              <Input
                label="Recovery code"
                name="recovery_code"
                autoComplete="one-time-code"
                required
                autoFocus
                value={data.recovery_code}
                error={errors.recovery_code}
                onChange={(event) => setData('recovery_code', event.target.value)}
              />
            ) : (
              <Input
                label="Authentication code"
                name="code"
                inputMode="numeric"
                autoComplete="one-time-code"
                maxLength={6}
                required
                autoFocus
                numeric
                value={data.code}
                error={errors.code}
                onChange={(event) => setData('code', event.target.value.replace(/\D/g, ''))}
              />
            )}

            <Button type="submit" variant="primary" size="lg" block loading={processing} className="mt-3">
              Verify
            </Button>
          </form>

          <button
            type="button"
            onClick={() => setUseRecovery((value) => !value)}
            className="mt-4 text-small text-accent hover:underline"
          >
            {useRecovery ? 'Use an authentication code instead' : 'Use a recovery code instead'}
          </button>
        </div>
      </div>
    </div>
  )
}

TwoFactorChallenge.layout = undefined

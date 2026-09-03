import { useEffect, useState, type FormEvent } from 'react'
import { Head, router, useForm } from '@inertiajs/react'
import { ShieldCheck } from 'lucide-react'
import { Button } from '@/ui/Button'
import { Input } from '@/ui/Field'

interface Props {
  enabled: boolean
  confirmed: boolean
}

/**
 * Enrolment is mandatory: until 2FA is confirmed, `EnsureTwoFactorIsEnabled`
 * redirects every other route here. Recovery codes are shown once, and the
 * user must confirm a live code before the session is released.
 */
export default function TwoFactorSetup({ enabled, confirmed }: Props) {
  const [qr, setQr] = useState<string | null>(null)
  const [secret, setSecret] = useState<string | null>(null)
  const [recoveryCodes, setRecoveryCodes] = useState<string[]>([])
  const [loading, setLoading] = useState(false)
  const { data, setData, post, processing, errors } = useForm({ code: '' })

  async function enable() {
    setLoading(true)
    router.post(
      '/user/two-factor-authentication',
      {},
      {
        preserveScroll: true,
        onFinish: () => {
          void loadSecrets()
          setLoading(false)
        },
      },
    )
  }

  async function loadSecrets() {
    const [qrResponse, codesResponse, keyResponse] = await Promise.all([
      fetch('/user/two-factor-qr-code', { headers: { Accept: 'application/json' } }),
      fetch('/user/two-factor-recovery-codes', { headers: { Accept: 'application/json' } }),
      fetch('/user/two-factor-secret-key', { headers: { Accept: 'application/json' } }),
    ])

    if (qrResponse.ok) setQr(((await qrResponse.json()) as { svg: string }).svg)
    if (codesResponse.ok) setRecoveryCodes((await codesResponse.json()) as string[])
    if (keyResponse.ok) setSecret(((await keyResponse.json()) as { secretKey: string }).secretKey)
  }

  useEffect(() => {
    if (enabled && !confirmed) void loadSecrets()
  }, [enabled, confirmed])

  function confirm(event: FormEvent) {
    event.preventDefault()
    post('/user/confirmed-two-factor-authentication', { preserveScroll: true })
  }

  return (
    <div className="flex min-h-screen items-center justify-center bg-deck px-4 py-12">
      <Head title="Set up two-factor authentication" />

      <div className="w-full max-w-[400px]">
        <img
          src="/images/walidia-logo.png"
          alt="Walidia Yachts"
          className="mx-auto mb-8 h-[44px] w-auto object-contain"
        />

        <div className="rounded-card border border-line bg-hull p-6">
          <span className="grid size-10 place-items-center rounded-card bg-accent-soft text-accent">
            <ShieldCheck className="size-5" aria-hidden />
          </span>

          <h1 className="mt-4 text-h1 text-ink">Set up two-factor authentication</h1>
          <p className="mt-2 text-body text-ink-soft">
            Every Walidia account requires it. Client records here include passport data, guest manifests and financial
            detail, so a password alone is not enough.
          </p>

          {!enabled && (
            <Button variant="primary" size="lg" className="mt-6" loading={loading} onClick={enable}>
              Begin setup
            </Button>
          )}

          {enabled && (
            <>
              <ol className="mt-6 flex flex-col gap-5">
                <li>
                  <p className="text-h3 text-ink">1 · Scan this code</p>
                  <p className="mt-1 text-small text-ink-soft">
                    Use Google Authenticator, Microsoft Authenticator, 1Password or any TOTP app.
                  </p>
                  {qr && (
                    <div
                      className="mt-3 inline-block rounded-card border border-line bg-white p-3 [&_svg]:size-[160px]"
                      dangerouslySetInnerHTML={{ __html: qr }}
                    />
                  )}
                  {secret && (
                    <p className="mt-2 text-small text-ink-faint">
                      Or enter this key manually: <span className="numeric text-ink-soft">{secret}</span>
                    </p>
                  )}
                </li>

                {recoveryCodes.length > 0 && (
                  <li>
                    <p className="text-h3 text-ink">2 · Save your recovery codes</p>
                    <p className="mt-1 text-small text-ink-soft">
                      Each code works once, and this is the only time they are shown. Store them somewhere you can reach
                      without this device.
                    </p>
                    <ul className="mt-3 grid grid-cols-2 gap-2 rounded-card border border-line bg-deck p-3">
                      {recoveryCodes.map((code) => (
                        <li key={code} className="numeric text-small text-ink">
                          {code}
                        </li>
                      ))}
                    </ul>
                  </li>
                )}

                <li>
                  <p className="text-h3 text-ink">3 · Confirm a code</p>
                  <form onSubmit={confirm} className="mt-3 flex items-end gap-3">
                    <Input
                      label="Authentication code"
                      inputMode="numeric"
                      maxLength={6}
                      required
                      numeric
                      className="max-w-[160px]"
                      value={data.code}
                      error={errors.code}
                      onChange={(event) => setData('code', event.target.value.replace(/\D/g, ''))}
                    />
                    <Button type="submit" variant="primary" loading={processing}>
                      Confirm
                    </Button>
                  </form>
                </li>
              </ol>
            </>
          )}
        </div>
      </div>
    </div>
  )
}

TwoFactorSetup.layout = undefined

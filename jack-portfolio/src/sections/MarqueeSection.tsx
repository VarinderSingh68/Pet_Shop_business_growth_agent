import { useEffect, useRef } from 'react'
import { motion, useAnimationFrame, useMotionValue } from 'framer-motion'
import { marqueeRow1, marqueeRow2 } from '../data/marqueeImages'

const tripledRow1 = [...marqueeRow1, ...marqueeRow1, ...marqueeRow1]
const tripledRow2 = [...marqueeRow2, ...marqueeRow2, ...marqueeRow2]

export default function MarqueeSection() {
  const sectionRef = useRef<HTMLElement>(null)
  const targetOffset = useRef(0)
  const x1 = useMotionValue(-200)
  const x2 = useMotionValue(200)

  useEffect(() => {
    const handleScroll = () => {
      const section = sectionRef.current
      if (!section) return

      const sectionTop = section.getBoundingClientRect().top + window.scrollY
      targetOffset.current = (window.scrollY - sectionTop + window.innerHeight) * 0.3
    }

    handleScroll()
    window.addEventListener('scroll', handleScroll, { passive: true })
    return () => window.removeEventListener('scroll', handleScroll)
  }, [])

  useAnimationFrame(() => {
    const target1 = targetOffset.current - 200
    const target2 = -(targetOffset.current - 200)
    x1.set(x1.get() + (target1 - x1.get()) * 0.07)
    x2.set(x2.get() + (target2 - x2.get()) * 0.07)
  })

  return (
    <section ref={sectionRef} className="overflow-hidden bg-[#0C0C0C] pb-10 pt-24 sm:pt-32 md:pt-40">
      <div className="flex flex-col gap-3">
        <motion.div className="flex gap-3" style={{ x: x1, willChange: 'transform' }}>
          {tripledRow1.map((src, i) => (
            <img
              key={`row1-${i}`}
              src={src}
              alt=""
              loading="lazy"
              className="h-[270px] w-[420px] flex-shrink-0 rounded-2xl object-cover transition-transform duration-300 ease-out hover:z-10 hover:scale-[1.04]"
            />
          ))}
        </motion.div>

        <motion.div className="flex gap-3" style={{ x: x2, willChange: 'transform' }}>
          {tripledRow2.map((src, i) => (
            <img
              key={`row2-${i}`}
              src={src}
              alt=""
              loading="lazy"
              className="h-[270px] w-[420px] flex-shrink-0 rounded-2xl object-cover transition-transform duration-300 ease-out hover:z-10 hover:scale-[1.04]"
            />
          ))}
        </motion.div>
      </div>
    </section>
  )
}

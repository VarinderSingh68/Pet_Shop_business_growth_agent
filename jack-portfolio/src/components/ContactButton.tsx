import { motion } from 'framer-motion'

export default function ContactButton() {
  return (
    <motion.button
      className="rounded-full px-8 py-3 text-xs font-medium uppercase tracking-widest text-white sm:px-10 sm:py-3.5 sm:text-sm md:px-12 md:py-4 md:text-base"
      style={{
        background:
          'linear-gradient(123deg, #18011F 7%, #B600A8 37%, #7621B0 72%, #BE4C00 100%)',
        boxShadow: '0px 4px 4px rgba(181, 1, 167, 0.25), 4px 4px 12px #7721B1 inset',
        outline: '2px solid #FFFFFF',
        outlineOffset: '-3px',
      }}
      whileHover={{
        scale: 1.05,
        boxShadow:
          '0px 6px 18px rgba(182, 0, 168, 0.45), 4px 4px 16px #7721B1 inset',
      }}
      whileTap={{ scale: 0.96 }}
      transition={{ type: 'spring', stiffness: 400, damping: 20 }}
    >
      Contact Me
    </motion.button>
  )
}
